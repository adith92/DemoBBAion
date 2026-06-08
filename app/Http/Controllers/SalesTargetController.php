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

        // Current user's own target
        $ownTarget = SalesTarget::getOrCreate($user->id, $year, $month);

        // Chart data: last 6 months for own user
        $chartData = $this->buildChartData($user->id, $year, $month);

        // For manager/gm/director: also get team targets
        $teamTargets = collect();
        $teamUsers   = collect();

        // Sort team by: name | revenue | kpi_pct
        $sortTeam = $request->get('sort_team', 'revenue');

        if ($user->isManager()) {
            $teamUsers = User::where('manager_id', $user->id)->where('role', 'sales')->orderBy('name')->get();
        } elseif ($user->isGM() || $user->isDirector()) {
            $teamUsers = User::whereIn('role', ['sales', 'manager'])->orderBy('name')->get();
        }

        if ($teamUsers->isNotEmpty()) {
            $userIds = $teamUsers->pluck('id');
            $teamTargets = SalesTarget::whereIn('user_id', $userIds)
                ->where('period_year', $year)
                ->where('period_month', $month)
                ->with('user')
                ->get()
                ->keyBy('user_id');

            // Ensure every team user has a record
            foreach ($teamUsers as $tu) {
                if (!$teamTargets->has($tu->id)) {
                    $newTarget = SalesTarget::getOrCreate($tu->id, $year, $month);
                    $newTarget->load('user');
                    $teamTargets->put($tu->id, $newTarget);
                }
            }
        }

        // Sort teamTargets collection
        if ($teamTargets->isNotEmpty()) {
            $teamTargets = match ($sortTeam) {
                'name'    => $teamTargets->sortBy(fn($t) => $t->user->name ?? ''),
                'kpi_pct' => $teamTargets->sortByDesc(fn($t) => $this->computeOverallScore($t)),
                default   => $teamTargets->sortByDesc(fn($t) => (float) ($t->actual_revenue ?? 0)),
            };
        }

        // Overall achievement (average of all KPIs for own target)
        $overallScore = $this->computeOverallScore($ownTarget);

        // Sales users list for the "Set Target" modal
        $salesUsers = User::whereIn('role', ['sales', 'manager'])->orderBy('name')->get();

        return view('kpi.index', compact(
            'ownTarget',
            'chartData',
            'teamTargets',
            'sortTeam',
            'teamUsers',
            'overallScore',
            'salesUsers',
            'year',
            'month'
        ));
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
