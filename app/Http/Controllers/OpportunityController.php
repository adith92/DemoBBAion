<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ApprovalRequest;
use App\Models\Client;
use App\Models\Opportunity;
use App\Models\Product;
use App\Models\User;
use App\Services\ApprovalService;
use App\Services\PipelineService;
use Illuminate\Http\Request;

class OpportunityController extends Controller
{
    public function __construct(
        protected PipelineService $pipelineService,
    ) {
        $this->middleware('auth');
        $this->middleware('role:gm,manager,sales');
    }

    // ------------------------------------------------------------------
    // Index — list with role-scoped visibility
    // ------------------------------------------------------------------

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Opportunity::with(['client', 'sales', 'product'])
            ->when(
                $user->isSales(),
                fn ($q) => $q->where('sales_id', $user->id)
            )
            ->when(
                $request->filled('stage'),
                fn ($q) => $q->where('stage', $request->stage)
            )
            ->when(
                $request->filled('client_id'),
                fn ($q) => $q->where('client_id', $request->client_id)
            )
            ->when(
                $request->filled('sales_id') && !$user->isSales(),
                fn ($q) => $q->where('sales_id', $request->sales_id)
            )
            ->latest();

        $opportunities = $query->paginate(20)->withQueryString();

        $clients    = Client::orderBy('company_name')->get(['id', 'company_name']);
        $salesUsers = User::where('role', 'sales')->orderBy('name')->get(['id', 'name']);

        return view('opportunities.index', compact('opportunities', 'clients', 'salesUsers'));
    }

    // ------------------------------------------------------------------
    // Create
    // ------------------------------------------------------------------

    public function create()
    {
        $user = auth()->user();

        $clients = Client::when(
                $user->isSales(),
                fn ($q) => $q->where('assigned_sales_id', $user->id)
            )
            ->orderBy('company_name')
            ->get(['id', 'company_name']);

        $products   = Product::active()->with('category')->orderBy('name')->get();
        $salesUsers = User::where('role', 'sales')->orderBy('name')->get(['id', 'name']);

        return view('pipeline.create', compact('clients', 'products', 'salesUsers'));
    }

    // ------------------------------------------------------------------
    // Store
    // ------------------------------------------------------------------

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'title'               => 'required|string|max:255',
            'client_id'           => 'required|exists:clients,id',
            'product_id'          => 'nullable|exists:products,id',
            'stage'               => 'in:prospecting,proposal,negotiation,won,lost',
            'estimated_value'     => 'nullable|numeric|min:0',
            'pax'                 => 'nullable|integer|min:1',
            'expected_close_date' => 'nullable|date',
            'notes'               => 'nullable|string',
        ]);

        // Force sales role to own record; manager/gm/director may assign
        if ($user->isSales()) {
            $validated['sales_id'] = $user->id;
        } else {
            $validated['sales_id'] = $request->input('sales_id', $user->id);
        }

        $validated['stage'] = $validated['stage'] ?? 'prospecting';

        $opportunity = Opportunity::create($validated);

        return redirect()->route('pipeline.index')
            ->with('success', "Opportunity {$opportunity->opp_number} berhasil dibuat.");
    }

    // ------------------------------------------------------------------
    // Show
    // ------------------------------------------------------------------

    public function show(Opportunity $opportunity)
    {
        $this->authorizeView($opportunity);

        $opportunity->load([
            'client',
            'sales',
            'product.category',
            'approver',
            'booking',
            'subscription',
        ]);

        $activityLogs = $opportunity->activityLogs()
            ->with('sales')
            ->latest('activity_date')
            ->take(5)
            ->get();

        $approvalRequests = $opportunity->approvalRequests()
            ->with(['requester', 'currentApprover'])
            ->latest()
            ->get();

        $nextStages = $this->pipelineService->getNextStages($opportunity->stage);

        return view('pipeline.show', compact(
            'opportunity',
            'activityLogs',
            'approvalRequests',
            'nextStages'
        ));
    }

    // ------------------------------------------------------------------
    // Update
    // ------------------------------------------------------------------

    public function update(Request $request, Opportunity $opportunity)
    {
        $this->authorizeEdit($opportunity);

        $validated = $request->validate([
            'title'               => 'required|string|max:255',
            'client_id'           => 'required|exists:clients,id',
            'product_id'          => 'nullable|exists:products,id',
            'stage'               => 'required|in:prospecting,proposal,negotiation,won,lost',
            'estimated_value'     => 'nullable|numeric|min:0',
            'final_value'         => 'nullable|numeric|min:0',
            'pax'                 => 'nullable|integer|min:1',
            'expected_close_date' => 'nullable|date',
            'actual_close_date'   => 'nullable|date',
            'lost_reason'         => 'nullable|string',
            'notes'               => 'nullable|string',
        ]);

        // Stage change validation
        if ($validated['stage'] !== $opportunity->stage) {
            if (!$this->pipelineService->canTransition($opportunity->stage, $validated['stage'])) {
                return back()->withErrors([
                    'stage' => "Tidak dapat berpindah dari {$opportunity->stage} ke {$validated['stage']}.",
                ]);
            }

            // Log stage transition as activity
            ActivityLog::create([
                'sales_id'       => auth()->id(),
                'client_id'      => $opportunity->client_id,
                'opportunity_id' => $opportunity->id,
                'type'           => 'follow_up',
                'subject'        => "Stage berubah: {$opportunity->stage} → {$validated['stage']}",
                'activity_date'  => now(),
            ]);

            if ($validated['stage'] === 'won') {
                $this->pipelineService->triggerWonActions($opportunity);
                $validated['actual_close_date'] = $validated['actual_close_date'] ?? now()->toDateString();
            }
        }

        $opportunity->update($validated);

        return back()->with('success', 'Opportunity berhasil diperbarui.');
    }

    // ------------------------------------------------------------------
    // Destroy
    // ------------------------------------------------------------------

    public function destroy(Opportunity $opportunity)
    {
        $this->authorizeEdit($opportunity);

        if (!in_array($opportunity->stage, ['prospecting', 'lost'])) {
            return back()->withErrors(['delete' => 'Hanya opportunity di stage Prospecting atau Lost yang dapat dihapus.']);
        }

        $opportunity->delete();

        return redirect()->route('pipeline.index')
            ->with('success', 'Opportunity berhasil dihapus.');
    }

    // ------------------------------------------------------------------
    // Advance Stage (POST)
    // ------------------------------------------------------------------

    public function advanceStage(Request $request, Opportunity $opportunity)
    {
        $this->authorizeEdit($opportunity);

        $validated = $request->validate([
            'stage'       => 'required|in:prospecting,proposal,negotiation,won,lost',
            'lost_reason' => 'required_if:stage,lost|nullable|string',
            'notes'       => 'nullable|string',
        ]);

        if (!$this->pipelineService->canTransition($opportunity->stage, $validated['stage'])) {
            return back()->withErrors([
                'stage' => "Transisi dari {$opportunity->stage} ke {$validated['stage']} tidak diizinkan.",
            ]);
        }

        // Log the stage advance as an activity
        ActivityLog::create([
            'sales_id'       => auth()->id(),
            'client_id'      => $opportunity->client_id,
            'opportunity_id' => $opportunity->id,
            'type'           => 'follow_up',
            'subject'        => "Stage diadvance: {$opportunity->stage} → {$validated['stage']}",
            'notes'          => $validated['notes'] ?? null,
            'activity_date'  => now(),
        ]);

        $updates = ['stage' => $validated['stage']];

        if ($validated['stage'] === 'lost' && !empty($validated['lost_reason'])) {
            $updates['lost_reason']        = $validated['lost_reason'];
            $updates['actual_close_date']  = now()->toDateString();
        }

        if ($validated['stage'] === 'won') {
            $updates['actual_close_date']  = now()->toDateString();
            $opportunity->update($updates);
            $this->pipelineService->triggerWonActions($opportunity->fresh());
            return back()->with('success', 'Selamat! Opportunity berhasil dimenangkan.');
        }

        $opportunity->update($updates);

        return back()->with('success', "Stage berhasil diubah ke {$validated['stage']}.");
    }

    // ------------------------------------------------------------------
    // Store Discount / Approval Request (POST)
    // ------------------------------------------------------------------

    public function storeDiscount(Request $request, Opportunity $opportunity)
    {
        $this->authorizeEdit($opportunity);

        $validated = $request->validate([
            'discount_percent' => 'required|numeric|min:0|max:100',
            'notes'            => 'nullable|string',
        ]);

        $discountPercent  = (float) $validated['discount_percent'];
        $estimatedValue   = (float) ($opportunity->estimated_value ?? 0);

        if (!ApprovalService::needsApproval($discountPercent)) {
            // Zero discount — just clear any existing discount
            $opportunity->update([
                'discount_percent'  => 0,
                'discount_approved' => true,
            ]);
            return back()->with('success', 'Diskon dihapus.');
        }

        // Update opportunity discount (pending approval)
        $opportunity->update([
            'discount_percent'  => $discountPercent,
            'discount_approved' => false,
        ]);

        $approvalRequest = ApprovalService::createApprovalChain($opportunity, $discountPercent);

        return back()->with('success', "Permintaan diskon {$discountPercent}% telah dikirim untuk persetujuan level {$approvalRequest->level}.");
    }

    // ------------------------------------------------------------------
    // Kanban drag-drop: move card to new stage (PATCH, JSON)
    // ------------------------------------------------------------------

    public function moveStage(Request $request, Opportunity $opportunity)
    {
        $this->authorizeEdit($opportunity);

        $validated = $request->validate([
            'stage'       => 'required|in:prospecting,proposal,negotiation,won,lost',
            'lost_reason' => 'nullable|string|max:500',
        ]);

        $fromStage = $opportunity->stage;
        $toStage   = $validated['stage'];

        if ($fromStage === $toStage) {
            return response()->json(['ok' => true, 'message' => 'No change.']);
        }

        if (!$this->pipelineService->canTransition($fromStage, $toStage)) {
            return response()->json([
                'ok'      => false,
                'message' => "Transisi dari {$fromStage} ke {$toStage} tidak diizinkan.",
            ], 422);
        }

        $updates = ['stage' => $toStage];

        if ($toStage === 'lost') {
            $updates['lost_reason']       = $validated['lost_reason'] ?? 'Dipindah via Kanban';
            $updates['actual_close_date'] = now()->toDateString();
        }

        if ($toStage === 'won') {
            $updates['actual_close_date'] = now()->toDateString();
        }

        $opportunity->update($updates);

        ActivityLog::create([
            'sales_id'       => auth()->id(),
            'client_id'      => $opportunity->client_id,
            'opportunity_id' => $opportunity->id,
            'type'           => 'follow_up',
            'subject'        => "Kanban: {$fromStage} → {$toStage}",
            'notes'          => "Dipindah via drag-drop kanban",
            'activity_date'  => now(),
        ]);

        if ($toStage === 'won') {
            $this->pipelineService->triggerWonActions($opportunity->fresh());
        }

        // Return per-stage summary so frontend can update counts + Rupiah values instantly
        $summary = Opportunity::selectRaw("stage, COUNT(*) as count, COALESCE(SUM(estimated_value),0) as total")
            ->groupBy('stage')
            ->get()
            ->keyBy('stage')
            ->map(fn($r) => ['count' => (int)$r->count, 'total' => (float)$r->total])
            ->toArray();

        return response()->json([
            'ok'      => true,
            'message' => "Deal dipindah ke {$toStage}.",
            'stage'   => $toStage,
            'summary' => $summary,
        ]);
    }

    // ------------------------------------------------------------------
    // Quick update (inline edit from Kanban card) — PATCH, JSON
    // ------------------------------------------------------------------

    public function quickUpdate(Request $request, Opportunity $opportunity)
    {
        $this->authorizeEdit($opportunity);

        $validated = $request->validate([
            'title'               => 'sometimes|required|string|max:255',
            'estimated_value'     => 'sometimes|nullable|numeric|min:0',
            'expected_close_date' => 'sometimes|nullable|date',
            'notes'               => 'sometimes|nullable|string',
            'pax'                 => 'sometimes|nullable|integer|min:1',
        ]);

        $opportunity->update($validated);

        return response()->json([
            'ok'          => true,
            'opportunity' => $opportunity->fresh(['client', 'sales', 'product']),
        ]);
    }

    // ------------------------------------------------------------------
    // 360° view data (GET, JSON)
    // ------------------------------------------------------------------

    public function view360(Opportunity $opportunity)
    {
        $this->authorizeView($opportunity);

        $opportunity->load([
            'client',
            'sales',
            'product.category',
            'approver',
            'activityLogs' => fn($q) => $q->latest()->limit(20),
            'activityLogs.sales',
            'approvalRequests' => fn($q) => $q->latest(),
            'booking',
            'subscription',
        ]);

        return response()->json([
            'ok'          => true,
            'opportunity' => $opportunity,
        ]);
    }

    // ------------------------------------------------------------------
    // Private helpers
    // ------------------------------------------------------------------

    protected function authorizeView(Opportunity $opportunity): void
    {
        $user = auth()->user();
        if ($user->isSales() && $opportunity->sales_id !== $user->id) {
            abort(403);
        }
    }

    protected function authorizeEdit(Opportunity $opportunity): void
    {
        $user = auth()->user();
        if ($user->isSales() && $opportunity->sales_id !== $user->id) {
            abort(403);
        }
    }
}
