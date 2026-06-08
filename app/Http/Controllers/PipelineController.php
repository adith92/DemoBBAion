<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Http\Request;

class PipelineController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:gm,manager,sales');
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $stages = ['prospecting', 'proposal', 'negotiation', 'won', 'lost'];

        // Build base query scoped by role
        $baseQuery = Opportunity::with(['client', 'sales', 'product', 'activityLogs'])
            ->when($user->isSales(), fn ($q) => $q->where('sales_id', $user->id));

        // Filter by sales (manager/gm/director only)
        if (!$user->isSales() && $request->filled('filter_sales')) {
            $baseQuery->where('sales_id', $request->filter_sales);
        }

        // Sort within each column
        $sortBy = $request->get('sort_by', 'updated');
        $baseQuery = match ($sortBy) {
            'value_desc' => $baseQuery->orderByDesc('estimated_value'),
            'value_asc'  => $baseQuery->orderBy('estimated_value'),
            'close_date' => $baseQuery->orderBy('expected_close_date'),
            'newest'     => $baseQuery->orderByDesc('created_at'),
            default      => $baseQuery->orderByDesc('updated_at'),
        };

        $allOpps = $baseQuery->get();

        // Group by stage, build kanban structure
        $kanban = [];
        foreach ($stages as $stage) {
            $stageOpps = $allOpps->where('stage', $stage)->values();
            $kanban[$stage] = [
                'opportunities' => $stageOpps,
                'count'         => $stageOpps->count(),
                'total_value'   => $stageOpps->sum(fn ($o) => (float) ($o->estimated_value ?? 0)),
            ];
        }

        // Sales users for filter dropdown
        $salesUsers = collect();
        if (!$user->isSales()) {
            if ($user->isManager()) {
                $salesUsers = User::where('manager_id', $user->id)->where('role', 'sales')->orderBy('name')->get();
            } else {
                $salesUsers = User::whereIn('role', ['sales', 'manager'])->orderBy('name')->get();
            }
        }

        return view('pipeline.index', compact('kanban', 'stages', 'salesUsers', 'sortBy'));
    }
}
