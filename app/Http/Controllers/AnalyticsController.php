<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use App\Models\Client;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\SalesTarget;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        // ── Pipeline value by stage ──────────────────────────────────────────
        try {
            $pipelineByStage = Opportunity::select(
                    'stage',
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(COALESCE(estimated_value, 0)) as total_value')
                )
                ->whereNotIn('stage', ['won', 'lost'])
                ->groupBy('stage')
                ->get()
                ->keyBy('stage');
        } catch (\Exception $e) {
            $pipelineByStage = collect();
        }

        // ── Top clients by revenue (paid invoices) ───────────────────────────
        // Use a safe subquery — avoids withSum() cross-DB issues
        try {
            $topClients = Client::select('clients.*')
                ->selectSub(
                    Invoice::select(DB::raw('COALESCE(SUM(amount), 0)'))
                        ->whereColumn('client_id', 'clients.id')
                        ->where('status', 'paid'),
                    'total_revenue'
                )
                ->orderByDesc('total_revenue')
                ->limit(10)
                ->get();
        } catch (\Exception $e) {
            $topClients = collect();
        }

        // ── Top sales by won deals ───────────────────────────────────────────
        try {
            $topSales = User::whereIn('role', ['sales', 'manager'])
                ->withCount(['opportunities as won_count' => fn($q) => $q->where('stage', 'won')])
                ->withSum(['opportunities as won_revenue' => fn($q) => $q->where('stage', 'won')], 'final_value')
                ->orderByDesc('won_count')
                ->limit(10)
                ->get();
        } catch (\Exception $e) {
            $topSales = collect();
        }

        // ── Activity summary last 30 days ────────────────────────────────────
        try {
            $activitySummary = ActivityLog::select('type', DB::raw('COUNT(*) as count'))
                ->where('activity_date', '>=', Carbon::now()->subDays(30))
                ->groupBy('type')
                ->get()
                ->keyBy('type');
        } catch (\Exception $e) {
            $activitySummary = collect();
        }

        // ── Cross-sell counts ────────────────────────────────────────────────
        try {
            $crossSellCount = $this->getCrossSellCounts();
        } catch (\Exception $e) {
            $crossSellCount = [
                'short_term_only' => 0, 'long_term_only' => 0, 'evoucher_only' => 0,
                'short_and_long'  => 0, 'short_and_ev'   => 0, 'long_and_ev'   => 0,
                'all_three'       => 0, 'none'            => 0,
            ];
        }

        return view('analytics.index', compact(
            'pipelineByStage',
            'topClients',
            'topSales',
            'activitySummary',
            'crossSellCount'
        ));
    }

    public function crosssell()
    {
        // NOTE: Product relation is called 'category()' not 'productCategory()'
        // We use optional() everywhere for safety
        try {
            $clients = Client::with([
                'opportunities' => fn($q) => $q->where('stage', 'won')->with('product.category'),
            ])->get();
        } catch (\Exception $e) {
            $clients = collect();
        }

        $shortTermOnly = collect();
        $longTermOnly  = collect();
        $evoucherOnly  = collect();
        $shortAndLong  = collect();
        $shortAndEv    = collect();
        $longAndEv     = collect();
        $allThree      = collect();
        $none          = collect();

        foreach ($clients as $client) {
            $types = $this->getClientProductTypes($client);

            $hasShort = in_array('short_term', $types);
            $hasLong  = in_array('long_term',  $types);
            $hasEv    = in_array('evoucher',   $types);

            if      ($hasShort && $hasLong && $hasEv) $allThree->push($client);
            elseif  ($hasShort && $hasLong)           $shortAndLong->push($client);
            elseif  ($hasShort && $hasEv)             $shortAndEv->push($client);
            elseif  ($hasLong  && $hasEv)             $longAndEv->push($client);
            elseif  ($hasShort)                       $shortTermOnly->push($client);
            elseif  ($hasLong)                        $longTermOnly->push($client);
            elseif  ($hasEv)                          $evoucherOnly->push($client);
            else                                      $none->push($client);
        }

        $clientTable = $clients->map(function ($client) {
            $types = $this->getClientProductTypes($client);
            return [
                'client'     => $client,
                'short_term' => in_array('short_term', $types),
                'long_term'  => in_array('long_term',  $types),
                'evoucher'   => in_array('evoucher',   $types),
            ];
        })->sortByDesc(fn($r) => (int)$r['short_term'] + (int)$r['long_term'] + (int)$r['evoucher']);

        return view('analytics.crosssell', compact(
            'shortTermOnly', 'longTermOnly', 'evoucherOnly',
            'shortAndLong', 'shortAndEv', 'longAndEv',
            'allThree', 'none', 'clientTable'
        ));
    }

    public function pipeline()
    {
        $stages = ['prospecting', 'proposal', 'negotiation', 'won', 'lost'];

        try {
            $stageData = Opportunity::select(
                    'stage',
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(COALESCE(estimated_value, 0)) as total_value'),
                    DB::raw('AVG(COALESCE(estimated_value, 0)) as avg_value')
                )
                ->groupBy('stage')
                ->get()
                ->keyBy('stage');
        } catch (\Exception $e) {
            $stageData = collect();
        }

        $counts = [];
        foreach ($stages as $stage) {
            $counts[$stage] = $stageData[$stage]->count ?? 0;
        }

        $conversionRates = [];
        $stageList = ['prospecting', 'proposal', 'negotiation'];
        foreach ($stageList as $i => $stage) {
            $next = $stageList[$i + 1] ?? null;
            if ($next) {
                $from = $counts[$stage] + $counts[$next];
                $conversionRates["{$stage}_to_{$next}"] = $from > 0
                    ? round(($counts[$next] / $from) * 100, 1)
                    : 0;
            }
        }

        $negotiationTotal = ($counts['negotiation'] ?? 0) + ($counts['won'] ?? 0) + ($counts['lost'] ?? 0);
        $conversionRates['negotiation_to_won'] = $negotiationTotal > 0
            ? round((($counts['won'] ?? 0) / $negotiationTotal) * 100, 1)
            : 0;

        $total = array_sum($counts);
        $overallWinRate = $total > 0 ? round((($counts['won'] ?? 0) / $total) * 100, 1) : 0;

        return view('analytics.pipeline', compact(
            'stages', 'stageData', 'counts', 'conversionRates', 'overallWinRate'
        ));
    }

    public function salesPerformance()
    {
        $now = Carbon::now();
        $salesUsers = User::whereIn('role', ['sales', 'manager'])->orderBy('name')->get();

        $performance = $salesUsers->map(function ($user) use ($now) {
            $opportunities = Opportunity::where('sales_id', $user->id);
            $total   = (clone $opportunities)->count();
            $won     = (clone $opportunities)->where('stage', 'won')->count();
            $lost    = (clone $opportunities)->where('stage', 'lost')->count();
            $revenue = (clone $opportunities)->where('stage', 'won')->sum('final_value');

            $winRate = ($won + $lost) > 0 ? round(($won / ($won + $lost)) * 100, 1) : 0;

            $target = SalesTarget::where('user_id', $user->id)
                ->where('period_year', $now->year)
                ->where('period_month', $now->month)
                ->first();

            $kpiPct = 0;
            if ($target && $target->target_revenue > 0) {
                $kpiPct = round(($target->actual_revenue / $target->target_revenue) * 100, 1);
            }

            return [
                'user'                => $user,
                'total_opportunities' => $total,
                'won'                 => $won,
                'lost'                => $lost,
                'win_rate'            => $winRate,
                'revenue'             => $revenue,
                'kpi_pct'             => $kpiPct,
                'target'              => $target,
            ];
        })->sortByDesc('won');

        return view('analytics.sales', compact('performance', 'now'));
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Get product category types for a client's won opportunities.
     * Uses 'category' relation (not 'productCategory') to match Product model.
     */
    private function getClientProductTypes($client): array
    {
        return $client->opportunities
            ->map(fn($o) => optional(optional($o->product)->category)->type)
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    private function getCrossSellCounts(): array
    {
        $clients = Client::with([
            'opportunities' => fn($q) => $q->where('stage', 'won')->with('product.category'),
        ])->get();

        $counts = [
            'short_term_only' => 0, 'long_term_only' => 0, 'evoucher_only' => 0,
            'short_and_long'  => 0, 'short_and_ev'   => 0, 'long_and_ev'   => 0,
            'all_three'       => 0, 'none'            => 0,
        ];

        foreach ($clients as $client) {
            $types = $this->getClientProductTypes($client);
            $hasS  = in_array('short_term', $types);
            $hasL  = in_array('long_term',  $types);
            $hasE  = in_array('evoucher',   $types);

            if      ($hasS && $hasL && $hasE) $counts['all_three']++;
            elseif  ($hasS && $hasL)          $counts['short_and_long']++;
            elseif  ($hasS && $hasE)          $counts['short_and_ev']++;
            elseif  ($hasL && $hasE)          $counts['long_and_ev']++;
            elseif  ($hasS)                   $counts['short_term_only']++;
            elseif  ($hasL)                   $counts['long_term_only']++;
            elseif  ($hasE)                   $counts['evoucher_only']++;
            else                              $counts['none']++;
        }

        return $counts;
    }
}
