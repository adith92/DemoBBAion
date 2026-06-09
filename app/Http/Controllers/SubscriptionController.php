<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:gm,finance,manager')->only(['create', 'store', 'terminate']);
    }

    public function index()
    {
        $status = request('status');
        $clientId = request('client_id');

        $subscriptions = Subscription::with(['client', 'vehicle', 'product', 'driver'])
            ->when($status, fn($q, $s) => $q->where('status', $s))
            ->when($clientId, fn($q, $c) => $q->where('client_id', $c))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $clients = Client::orderBy('company_name')->get();

        $dueTodayCount = Subscription::where('status', 'active')
            ->where('next_billing_date', '<=', today())
            ->count();

        return view('subscriptions.index', compact('subscriptions', 'clients', 'status', 'clientId', 'dueTodayCount'));
    }

    public function create()
    {
        $clients = Client::where('status', 'active')->orderBy('company_name')->get();
        $vehicles = Vehicle::where('status', 'available')->orderBy('plate_number')->get();
        $drivers = Driver::where('status', 'available')->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('subscriptions.create', compact('clients', 'vehicles', 'drivers', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'     => 'required|exists:clients,id',
            'product_id'    => 'nullable|exists:products,id',
            'vehicle_id'    => 'nullable|exists:vehicles,id',
            'driver_id'     => 'nullable|exists:drivers,id',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after:start_date',
            'monthly_rate'  => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,quarterly,yearly',
            'auto_renew'    => 'boolean',
            'notes'         => 'nullable|string',
        ]);

        $validated['auto_renew'] = $request->boolean('auto_renew', true);
        $validated['status'] = 'active';
        $validated['next_billing_date'] = $validated['start_date'];

        $subscription = Subscription::create($validated);

        return redirect()
            ->route('subscriptions.show', $subscription)
            ->with('success', 'Kontrak berlangganan berhasil dibuat: ' . $subscription->sub_number);
    }

    public function show(Subscription $subscription)
    {
        $subscription->load(['client', 'vehicle', 'driver', 'product', 'opportunity']);

        $invoices = Invoice::where('client_id', $subscription->client_id)
            ->where('notes', 'like', '%' . $subscription->sub_number . '%')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('subscriptions.show', compact('subscription', 'invoices'));
    }

    public function update(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'client_id'     => 'required|exists:clients,id',
            'product_id'    => 'nullable|exists:products,id',
            'vehicle_id'    => 'nullable|exists:vehicles,id',
            'driver_id'     => 'nullable|exists:drivers,id',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after:start_date',
            'monthly_rate'  => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,quarterly,yearly',
            'status'        => 'required|in:active,paused,terminated,expired',
            'auto_renew'    => 'boolean',
            'notes'         => 'nullable|string',
        ]);

        $validated['auto_renew'] = $request->boolean('auto_renew');

        $subscription->update($validated);

        return redirect()
            ->route('subscriptions.show', $subscription)
            ->with('success', 'Kontrak berlangganan berhasil diperbarui.');
    }

    public function terminate(Request $request, Subscription $subscription)
    {
        if ($subscription->status === 'terminated') {
            return back()->with('error', 'Kontrak sudah diterminasi.');
        }

        $request->validate([
            'pin' => 'required|string|size:6',
        ]);

        $user = auth()->user();
        if (!\Illuminate\Support\Facades\Hash::check($request->pin, $user->billing_pin)) {
            return back()->with('error', 'PIN Konfirmasi Terminasi salah.');
        }

        $subscription->update([
            'status'   => 'terminated',
            'end_date' => today(),
        ]);

        return back()->with('success', 'Kontrak ' . $subscription->sub_number . ' berhasil diterminasi.');
    }

    /**
     * Process subscription billing for all due subscriptions.
     * Called by cron command or manually by admin.
     */
    public static function processMonthlyBilling(): array
    {
        $results = ['processed' => 0, 'skipped' => 0, 'errors' => 0];

        $dueSubscriptions = Subscription::with(['client'])
            ->where('status', 'active')
            ->where('next_billing_date', '<=', today())
            ->get();

        Log::info('[SubscriptionBilling] Found ' . $dueSubscriptions->count() . ' due subscriptions.');

        foreach ($dueSubscriptions as $subscription) {
            try {
                // Idempotency guard: check if invoice already exists for this billing period
                $periodLabel = 'SUB-BILLING/' . $subscription->sub_number . '/' . $subscription->next_billing_date->format('Ym');

                $existingInvoice = Invoice::where('client_id', $subscription->client_id)
                    ->where('notes', 'like', '%' . $periodLabel . '%')
                    ->first();

                if ($existingInvoice) {
                    Log::info("[SubscriptionBilling] Skipped (already billed): {$subscription->sub_number} period {$subscription->next_billing_date->format('Y-m')}");
                    $results['skipped']++;
                    continue;
                }

                // Calculate invoice amount based on billing cycle
                $amount = match ($subscription->billing_cycle) {
                    'quarterly' => $subscription->monthly_rate * 3,
                    'yearly'    => $subscription->monthly_rate * 12,
                    default     => $subscription->monthly_rate,
                };

                // Generate invoice number
                $yearMonth = now()->format('Ym');
                $prefix = 'INV-' . $yearMonth . '-';
                $lastInvoice = Invoice::where('invoice_number', 'like', $prefix . '%')
                    ->orderByDesc('invoice_number')
                    ->first();
                $seq = $lastInvoice ? ((int) substr($lastInvoice->invoice_number, -4)) + 1 : 1;
                $invoiceNumber = $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);

                // Create invoice
                Invoice::create([
                    'invoice_number' => $invoiceNumber,
                    'booking_id'     => null,
                    'client_id'      => $subscription->client_id,
                    'amount'         => $amount,
                    'status'         => 'sent',
                    'due_date'       => today()->addDays(14),
                    'notes'          => "Auto-generated subscription billing. {$periodLabel}",
                ]);

                // Advance next_billing_date
                $nextDate = match ($subscription->billing_cycle) {
                    'quarterly' => $subscription->next_billing_date->addMonths(3),
                    'yearly'    => $subscription->next_billing_date->addYear(),
                    default     => $subscription->next_billing_date->addMonth(),
                };

                // Check if subscription has expired
                $newStatus = $subscription->status;
                if ($subscription->end_date && $nextDate->gt($subscription->end_date)) {
                    if (!$subscription->auto_renew) {
                        $newStatus = 'expired';
                    }
                }

                $subscription->update([
                    'last_billed_at'    => today(),
                    'next_billing_date' => $nextDate,
                    'status'            => $newStatus,
                ]);

                Log::info("[SubscriptionBilling] Billed: {$subscription->sub_number} | Amount: {$amount} | Next: {$nextDate->toDateString()}");
                $results['processed']++;

            } catch (\Exception $e) {
                Log::error("[SubscriptionBilling] Error processing {$subscription->sub_number}: " . $e->getMessage());
                $results['errors']++;
            }
        }

        Log::info('[SubscriptionBilling] Done. Processed: ' . $results['processed'] . ', Skipped: ' . $results['skipped'] . ', Errors: ' . $results['errors']);

        return $results;
    }

    /**
     * HTTP handler for manual billing run (POST /subscriptions/billing/run).
     * Extracted from route Closure so route:cache works in production.
     */
    public function runBilling()
    {
        $results = static::processMonthlyBilling();
        return back()->with('success',
            "Billing selesai: {$results['processed']} diproses, {$results['skipped']} dilewati, {$results['errors']} error."
        );
    }
}
