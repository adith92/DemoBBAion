<?php

namespace App\Http\Controllers;

use App\Models\SalesTarget;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SalesTargetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:gm,manager,sales')->only('index');
        $this->middleware('role:gm,manager')->only('store');
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $year  = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        $users = User::all()->map(function($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'role' => $u->role === 'sales' ? 'Sales' : ($u->role === 'manager' ? 'Manager' : 'GM'),
                'managerId' => $u->manager_id,
            ];
        });

        $deals = \App\Models\Opportunity::all()->map(function($d) {
            return [
                'id' => $d->id,
                'salesId' => $d->sales_id,
                'stage' => $d->stage === 'won' ? 'Won' : ($d->stage === 'lost' ? 'Lost' : 'Active'),
                'actualValue' => (float)$d->final_value,
                'estimatedValue' => (float)$d->estimated_value,
                'products' => $d->products, // JSON array
            ];
        });

        $dbTargets = SalesTarget::where('period_year', $year)
            ->where('period_month', $month)
            ->get();

        $targets = $dbTargets->map(function($t) {
            return [
                'userId' => $t->user_id,
                'productTargets' => [
                    'Mobil Short Term' => (float)$t->target_revenue * 0.4,
                    'Bis Short Term'   => (float)$t->target_revenue * 0.2,
                    'Mobil Long Term'  => (float)$t->target_revenue * 0.15,
                    'Bis Long Term'    => (float)$t->target_revenue * 0.1,
                    'E-Voucher'        => (float)$t->target_revenue * 0.1,
                    'Supir'            => (float)$t->target_revenue * 0.05,
                ]
            ];
        });

        $currentUser = [
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->role === 'sales' ? 'Sales' : ($user->role === 'manager' ? 'Manager' : 'GM'),
            'managerId' => $user->manager_id,
        ];

        return view('kpi.index', compact('users', 'deals', 'targets', 'currentUser', 'year', 'month'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'              => 'required|exists:users,id',
            'period_year'          => 'required|integer|min:2020|max:2100',
            'period_month'         => 'required|integer|min:1|max:12',
            'target_meetings'      => 'nullable|integer|min:0',
            'target_calls'         => 'nullable|integer|min:0',
            'target_visits'        => 'nullable|integer|min:0',
            'target_opportunities' => 'nullable|integer|min:0',
            'target_won'           => 'nullable|integer|min:0',
            'target_revenue'       => 'nullable|numeric|min:0',
        ]);

        // Manager can only set for their own subordinates
        $authUser = auth()->user();
        if ($authUser->isManager()) {
            $subordinateIds = User::where('manager_id', $authUser->id)->pluck('id');
            if (!$subordinateIds->contains($validated['user_id']) && $validated['user_id'] != $authUser->id) {
                abort(403, 'Anda hanya bisa menetapkan target untuk anggota tim Anda.');
            }
        }

        SalesTarget::updateOrCreate(
            [
                'user_id'      => $validated['user_id'],
                'period_year'  => $validated['period_year'],
                'period_month' => $validated['period_month'],
            ],
            [
                'target_meetings'      => $validated['target_meetings'] ?? 0,
                'target_calls'         => $validated['target_calls'] ?? 0,
                'target_visits'        => $validated['target_visits'] ?? 0,
                'target_opportunities' => $validated['target_opportunities'] ?? 0,
                'target_won'           => $validated['target_won'] ?? 0,
                'target_revenue'       => $validated['target_revenue'] ?? 0,
            ]
        );

        return redirect()
            ->route('kpi.index', ['year' => $validated['period_year'], 'month' => $validated['period_month']])
            ->with('success', 'Target KPI berhasil disimpan.');
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function buildChartData(int $userId, int $currentYear, int $currentMonth): array
    {
        $labels   = [];
        $meetings = [];
        $calls    = [];
        $revenue  = [];

        // Build last 6 months including the selected month
        $base = Carbon::create($currentYear, $currentMonth, 1);

        for ($i = 5; $i >= 0; $i--) {
            $date  = $base->copy()->subMonths($i);
            $y     = (int) $date->year;
            $m     = (int) $date->month;
            $target = SalesTarget::where('user_id', $userId)
                ->where('period_year', $y)
                ->where('period_month', $m)
                ->first();

            $labels[]   = $date->translatedFormat('M Y');
            $meetings[] = $target?->actual_meetings ?? 0;
            $calls[]    = $target?->actual_calls ?? 0;
            $revenue[]  = (float) ($target?->actual_revenue ?? 0);
        }

        return compact('labels', 'meetings', 'calls', 'revenue');
    }

    private function computeOverallScore(SalesTarget $target): float
    {
        $scores = [];

        if ($target->target_meetings > 0) {
            $scores[] = min(100, ($target->actual_meetings / $target->target_meetings) * 100);
        }
        if ($target->target_calls > 0) {
            $scores[] = min(100, ($target->actual_calls / $target->target_calls) * 100);
        }
        if ($target->target_visits > 0) {
            $scores[] = min(100, ($target->actual_visits / $target->target_visits) * 100);
        }
        if ($target->target_opportunities > 0) {
            $scores[] = min(100, ($target->actual_opportunities / $target->target_opportunities) * 100);
        }
        if ($target->target_won > 0) {
            $scores[] = min(100, ($target->actual_won / $target->target_won) * 100);
        }
        if ((float) $target->target_revenue > 0) {
            $scores[] = min(100, ((float) $target->actual_revenue / (float) $target->target_revenue) * 100);
        }

        if (empty($scores)) {
            return 0;
        }

        return round(array_sum($scores) / count($scores), 1);
    }
}
