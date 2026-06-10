<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Booking;
use App\Models\Invoice;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Client::with(['assignedSales', 'invoices'])
            ->withCount(['opportunities as won_opportunities_count' => function ($q) {
                $q->where('stage', 'won');
            }])
            ->withSum(['opportunities as won_opportunities_sum' => function ($q) {
                $q->where('stage', 'won');
            }], 'final_value')
            ->when($user->isSales(), fn($q) => $q->where('assigned_sales_id', $user->id));

        // Filter status
        if ($request->filled('filter_status')) {
            $status = $request->input('filter_status');
            if (in_array($status, ['active', 'inactive'])) {
                $query->where('status', $status);
            }
        }

        // Sort
        $sortBy = $request->input('sort_by', 'name_asc');
        if ($sortBy === 'transactions_desc') {
            $query->orderByDesc('won_opportunities_count');
        } elseif ($sortBy === 'value_desc') {
            $query->orderByDesc('won_opportunities_sum');
        } elseif ($sortBy === 'name_desc') {
            $query->orderBy('company_name', 'desc');
        } else {
            $query->orderBy('company_name', 'asc');
        }

        $clients = $query->paginate(20)->withQueryString();

        return view('clients.index', compact('clients'));
    }

    public function show(Client $client)
    {
        $user = auth()->user();

        // Sales can only see their own clients
        if ($user->isSales() && $client->assigned_sales_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        // Ops cannot access client profiles
        if ($user->isOperational()) {
            abort(403, 'Unauthorized');
        }

        $client->load([
            'assignedSales',
            'invoices.payments',
            'meetingLogs',
            'opportunities.product',
        ]);

        $stats = [
            'total_spend'   => $client->invoices->where('status', 'paid')->sum('amount'),
            'total_pending' => $client->invoices->whereIn('status', ['sent', 'draft'])->sum('amount'),
            'total_overdue' => $client->invoices->where('status', 'overdue')->sum('amount'),
            'won_deals_count' => $client->opportunities->where('stage', 'won')->count(),
            'won_deals_sum'   => $client->opportunities->where('stage', 'won')->sum('final_value'),
        ];

        return view('clients.show', compact('client', 'stats'));
    }
}
