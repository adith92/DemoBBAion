<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Client;
use App\Models\PurchaseOrder;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\Opportunity;
use App\Models\ActivityLog;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Route each role to their own dashboard with the correct data.
     */
    public function index()
    {
        $user = Auth::user();
        $role = $user->role ?? 'sales';

        return match ($role) {
            'gm'          => $this->gm(),
            'manager'     => $this->manager(),
            'sales'       => $this->sales(),
            'operational' => $this->operational(),
            'finance'     => $this->finance(),
            default       => $this->gm(),
        };
    }

    /* ------------------------------------------------------------------ */
    /* GM                                                                   */
    /* ------------------------------------------------------------------ */
    public function gm()
    {
        $today      = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd   = Carbon::now()->endOfMonth();

        $availableVehicles  = Vehicle::where('status', 'available')->count();
        $pendingDispatch    = Booking::where('status', 'pending')->count();
        
        // Count meetings in the last 7 days + next 7 days from ActivityLog (type = meeting)
        $upcomingMeetings   = ActivityLog::where('type', 'meeting')
                                  ->whereBetween('activity_date', [Carbon::now()->subDays(7)->startOfDay(), Carbon::now()->addDays(7)->endOfDay()])
                                  ->count();

        // Dynamic target vs realization (Booked vs Paid)
        $totalMonthlyBooked = Opportunity::where('stage', 'won')
                                  ->whereBetween('actual_close_date', [$monthStart, $monthEnd])
                                  ->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(final_value, estimated_value, 0)'));

        $totalMonthlyPaid   = Invoice::where('status', 'paid')
                                  ->whereBetween('updated_at', [$monthStart, $monthEnd])
                                  ->sum('amount');

        $outstandingInvoices = Invoice::whereIn('status', ['sent', 'draft', 'overdue'])->sum('amount');

        $completedBookings   = Booking::whereMonth('created_at', Carbon::now()->month)
                                  ->where('status', 'completed')->count();

        $activeClients = Client::where('status', 'active')->count();
        $todayRevenue  = Booking::whereDate('created_at', $today)
                            ->where('status', 'completed')->sum('price');

        // Dynamic Active Bookings Count
        $activeBookings = Booking::whereIn('status', ['confirmed', 'active'])->count();

        // 1. Recent Bookings from DB
        $recentBookings = Booking::with('client')
            ->latest()
            ->take(4)
            ->get()
            ->map(function($b) {
                return [
                    'id' => $b->booking_number,
                    'client' => $b->client->company_name ?? 'Walk-in Client',
                    'fleet' => $b->vehicle_type ?? 'Golden Bird',
                    'status' => ucfirst($b->status),
                    'statusClass' => match($b->status) {
                        'completed' => 'status-completed',
                        'active', 'confirmed' => 'status-confirmed',
                        'pending' => 'status-pending',
                        default => 'status-pending'
                    }
                ];
            })
            ->toArray();

        // 2. 7-Day Revenue & Deals Timeline
        $days7 = [];
        $days7Labels = [];
        $days7Revenue = [];
        $days7Deals = [];
        $dayNames = [
            'Sunday' => 'Ming', 'Monday' => 'Sen', 'Tuesday' => 'Sel',
            'Wednesday' => 'Rab', 'Thursday' => 'Kam', 'Friday' => 'Jum', 'Saturday' => 'Sab'
        ];

        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i);
            $dayName = $dayNames[$day->format('l')] ?? $day->format('D');
            $dateStr = $day->format('j M');

            $dealsClosedCount = Opportunity::where('stage', 'won')
                ->whereDate('actual_close_date', $day)
                ->count();

            $revenueVal = Opportunity::where('stage', 'won')
                ->whereDate('actual_close_date', $day)
                ->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(final_value, estimated_value, 0)'));
            $revenueJuta = round($revenueVal / 1000000, 1);

            $days7[] = [
                'day' => $dayName,
                'date' => $dateStr,
                'deals' => $dealsClosedCount,
                'won' => $dealsClosedCount,
                'today' => $i === 0
            ];

            $days7Labels[] = $dayName . ' ' . $day->format('j/n');
            $days7Revenue[] = $revenueJuta;
            $days7Deals[] = $dealsClosedCount;
        }

        // 3. Pipeline Stage Donut
        $stageLabelsMap = [
            'call_meeting' => 'Call Meeting',
            'prospecting' => 'Prospecting',
            'proposal' => 'Proposal',
            'negotiation' => 'Negotiation',
            'won' => 'Won',
            'lost' => 'Lost'
        ];
        $stageColorsMap = [
            'call_meeting' => '#a78bfa',
            'prospecting' => '#6366f1',
            'proposal' => '#f59e0b',
            'negotiation' => '#f97316',
            'won' => '#10b981',
            'lost' => '#ef4444'
        ];

        $totalOpps = Opportunity::count();
        $pipelineDistribution = [];
        $pipelineLabels = [];
        $pipelinePct = [];
        $pipelineColors = [];

        foreach ($stageLabelsMap as $stage => $label) {
            $count = Opportunity::where('stage', $stage)->count();
            $pct = $totalOpps > 0 ? round(($count / $totalOpps) * 100) : 0;

            $pipelineDistribution[] = [
                'label' => $label,
                'pct' => $pct,
                'color' => $stageColorsMap[$stage],
                'count' => $count
            ];

            $pipelineLabels[] = $label;
            $pipelinePct[] = $pct;
            $pipelineColors[] = $stageColorsMap[$stage];
        }

        // 4. Sales Leaderboard
        $leaderboard = Opportunity::where('stage', 'won')
            ->whereBetween('actual_close_date', [$monthStart, $monthEnd])
            ->selectRaw('sales_id, SUM(COALESCE(final_value, estimated_value, 0)) as total_revenue')
            ->groupBy('sales_id')
            ->orderByDesc('total_revenue')
            ->with('sales:id,name')
            ->take(5)
            ->get();

        $salesLeaderboardLabels = [];
        $salesLeaderboardData = [];
        $leaderboardColors = [
            'rgba(245,158,11,0.7)', 'rgba(148,163,184,0.6)', 'rgba(180,83,9,0.6)',
            'rgba(99,102,241,0.55)', 'rgba(99,102,241,0.4)'
        ];
        $salesLeaderboardColors = [];

        foreach ($leaderboard as $idx => $row) {
            $salesName = $row->sales->name ?? 'Unknown Sales';
            $salesLeaderboardLabels[] = explode(' ', $salesName)[0];
            $salesLeaderboardData[] = round($row->total_revenue / 1000000, 1);
            $salesLeaderboardColors[] = $leaderboardColors[$idx] ?? 'rgba(99,102,241,0.4)';
        }

        if (count($salesLeaderboardLabels) === 0) {
            $salesLeaderboardLabels = ['No Data'];
            $salesLeaderboardData = [0];
            $salesLeaderboardColors = ['rgba(99,102,241,0.4)'];
        }

        // 5. Activity Breakdown
        $activityTypes = [
            'call' => ['label' => '📞 Call', 'color' => '#3b82f6'],
            'email' => ['label' => '📧 Email', 'color' => '#8b5cf6'],
            'meeting' => ['label' => '🤝 Meeting', 'color' => '#10b981'],
            'proposal' => ['label' => '📄 Proposal', 'color' => '#f59e0b'],
            'follow_up' => ['label' => '🔄 Follow-up', 'color' => '#ec4899']
        ];

        $actTypes = [];
        $activityChartLabels = [];
        $activityChartData = [];

        foreach ($activityTypes as $type => $info) {
            $count = ActivityLog::where('type', $type)
                ->whereBetween('activity_date', [Carbon::now()->subDays(6)->startOfDay(), Carbon::now()->endOfDay()])
                ->count();

            $actTypes[] = [
                'label' => $info['label'],
                'count' => $count,
                'color' => $info['color']
            ];

            $activityChartLabels[] = str_replace(['📞 ', '📧 ', '🤝 ', '📄 ', '🔄 '], '', $info['label']);
            $activityChartData[] = $count;
        }

        // 6. Sparklines & Trends
        $sparkRevenue = []; $sparkBookings = []; $sparkFleet = []; $sparkClients = []; $sparkInvoice = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = Carbon::now()->subMonths($i);
            $start = $m->copy()->startOfMonth();
            $end = $m->copy()->endOfMonth();

            $revVal = Opportunity::where('stage', 'won')
                ->whereBetween('actual_close_date', [$start, $end])
                ->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(final_value, estimated_value, 0)'));
            $sparkRevenue[] = round($revVal / 1000000, 1);

            $sparkBookings[] = Booking::whereBetween('created_at', [$start, $end])->count();

            $totalFleet = Vehicle::count();
            $activeFleet = Vehicle::whereIn('status', ['available', 'on_trip'])->count();
            $sparkFleet[] = $totalFleet > 0 ? round(($activeFleet / $totalFleet) * 100) : 75;

            $sparkClients[] = Client::where('created_at', '<=', $end)->count();

            $invVal = Invoice::whereIn('status', ['sent', 'draft', 'overdue'])
                ->whereBetween('created_at', [$start, $end])
                ->sum('amount');
            $sparkInvoice[] = round($invVal / 1000000, 1);
        }

        // Booking signals (increase/decrease vs last month)
        $thisMonthBookings = Booking::whereMonth('created_at', Carbon::now()->month)->count();
        $lastMonthBookings = Booking::whereMonth('created_at', Carbon::now()->subMonth()->month)->count();
        $bookingsDiff = $thisMonthBookings - $lastMonthBookings;
        $bookingsSignal = $bookingsDiff >= 0 ? "▲ " . $bookingsDiff : "▼ " . abs($bookingsDiff);

        // Clients signals
        $thisMonthClients = Client::whereMonth('created_at', Carbon::now()->month)->count();
        $clientsSignal = $thisMonthClients > 0 ? "▲ " . $thisMonthClients : "—";

        // Fleet utilization rate
        $totalVehicles = Vehicle::count();
        $availableVehiclesCount = Vehicle::where('status', 'available')->count();
        $onTripVehiclesCount = Vehicle::where('status', 'on_trip')->count();
        $utilizationRate = $totalVehicles > 0 ? round((($onTripVehiclesCount + $availableVehiclesCount) / $totalVehicles) * 100) : 75;

        // Fleet League by Pool
        $pools = \App\Models\Pool::withCount([
            'vehicles',
            'vehicles as active_vehicles' => function($q) {
                $q->whereIn('status', ['available', 'on_trip']);
            }
        ])->get();

        $fleetLeagueColors = ['#f59e0b', '#10b981', '#3b82f6', '#8b5cf6'];
        $fleetLeagueBadges = [
            ['badge' => 'High Performer', 'bg' => 'rgba(245,158,11,0.12)', 'text' => '#fbbf24'],
            ['badge' => 'Stable', 'bg' => 'rgba(16,185,129,0.12)', 'text' => '#34d399'],
            ['badge' => 'Needs Growth', 'bg' => 'rgba(59,130,246,0.12)', 'text' => '#60a5fa'],
            ['badge' => 'Under Review', 'bg' => 'rgba(139,92,246,0.12)', 'text' => '#a78bfa']
        ];

        $fleetLeague = [];
        foreach ($pools as $index => $pool) {
            $pct = $pool->vehicles_count > 0 ? round(($pool->active_vehicles / $pool->vehicles_count) * 100) : 75;
            $badgeIdx = min($index, 3);
            
            $fleetName = match($pool->name) {
                'Pool Jakarta' => 'Golden Bird',
                'Pool Bandung' => 'Big Bird',
                'Pool Surabaya' => 'Cititrans',
                default => str_replace('Pool ', '', $pool->name)
            };

            $fleetLeague[] = [
                'name' => $fleetName,
                'pct' => $pct,
                'color' => $fleetLeagueColors[$badgeIdx] ?? '#6b7280',
                'badge' => $fleetLeagueBadges[$badgeIdx]['badge'],
                'badgeColor' => $fleetLeagueBadges[$badgeIdx]['bg'],
                'badgeText' => $fleetLeagueBadges[$badgeIdx]['text']
            ];
        }

        if (count($fleetLeague) === 0) {
            $fleetLeague = [
                ['name'=>'Golden Bird','pct'=>92,'color'=>'#f59e0b','badge'=>'High Performer','badgeColor'=>'rgba(245,158,11,0.12)','badgeText'=>'#fbbf24'],
                ['name'=>'Big Bird','pct'=>84,'color'=>'#10b981','badge'=>'Stable','badgeColor'=>'rgba(16,185,129,0.12)','badgeText'=>'#34d399'],
                ['name'=>'Cititrans','pct'=>78,'color'=>'#3b82f6','badge'=>'Needs Growth','badgeColor'=>'rgba(59,130,246,0.12)','badgeText'=>'#60a5fa'],
                ['name'=>'Exec. Transport','pct'=>73,'color'=>'#8b5cf6','badge'=>'Under Review','badgeColor'=>'rgba(139,92,246,0.12)','badgeText'=>'#a78bfa']
            ];
        }

        // 7. Weekly Revenue movement data
        $weeklyRevenue = [];
        $weeklyLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i);
            $weeklyLabels[] = $day->format('D');
            
            $val = Opportunity::where('stage', 'won')
                ->whereDate('actual_close_date', $day)
                ->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(final_value, estimated_value, 0)'));
            $weeklyRevenue[] = round($val / 1000000, 1);
        }

        // Tambahan data performa sales untuk GM
        $users = User::all()->map(function($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'role' => $u->role === 'sales' ? 'Sales' : ($u->role === 'manager' ? 'Manager' : 'GM'),
                'managerId' => $u->manager_id,
            ];
        });

        $deals = Opportunity::all()->map(function($d) {
            return [
                'id' => $d->id,
                'salesId' => $d->sales_id,
                'stage' => $d->stage === 'won' ? 'Won' : ($d->stage === 'lost' ? 'Lost' : 'Active'),
                'actualValue' => (float)$d->final_value,
                'estimatedValue' => (float)$d->estimated_value,
                'products' => $d->products,
            ];
        });

        $year  = now()->year;
        $month = now()->month;
        $dbTargets = \App\Models\SalesTarget::where('period_year', $year)
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

        return view('dashboard.gm', compact(
            'availableVehicles', 'pendingDispatch', 'upcomingMeetings',
            'totalMonthlyBooked', 'totalMonthlyPaid', 'outstandingInvoices',
            'completedBookings', 'activeClients', 'todayRevenue', 'users', 'deals', 'targets',
            'recentBookings', 'days7', 'days7Labels', 'days7Revenue', 'days7Deals',
            'pipelineDistribution', 'pipelineLabels', 'pipelinePct', 'pipelineColors',
            'salesLeaderboardLabels', 'salesLeaderboardData', 'salesLeaderboardColors',
            'actTypes', 'activityChartLabels', 'activityChartData',
            'sparkRevenue', 'sparkBookings', 'sparkFleet', 'sparkClients', 'sparkInvoice',
            'bookingsSignal', 'clientsSignal', 'utilizationRate', 'fleetLeague',
            'weeklyRevenue', 'weeklyLabels', 'activeBookings'
        ));
    }

    /* ------------------------------------------------------------------ */
    /* MANAGER                                                              */
    /* ------------------------------------------------------------------ */
    public function manager()
    {
        $user       = auth()->user();
        $now        = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();

        $teamQuery = User::where('role', 'sales');
        if ($user->isManager()) {
            $teamQuery->where('manager_id', $user->id);
        }

        $teamMembers = $teamQuery->get()->map(function ($s) use ($now) {
            $won   = Opportunity::where('sales_id', $s->id)->whereMonth('actual_close_date', $now->month)->whereYear('actual_close_date', $now->year)->where('stage', 'won')->count();
            $lost  = Opportunity::where('sales_id', $s->id)->whereMonth('actual_close_date', $now->month)->whereYear('actual_close_date', $now->year)->where('stage', 'lost')->count();
            $total = $won + $lost;
            $s->won_count      = $won;
            $s->lost_count     = $lost;
            $s->pipeline_value = Opportunity::where('sales_id', $s->id)->whereNotIn('stage', ['won', 'lost'])->sum('estimated_value') ?? 0;
            $s->win_rate       = $total > 0 ? round($won / $total * 100) : 0;
            return $s;
        });

        $teamPipelineValue = $teamMembers->sum('pipeline_value');
        $teamWon           = $teamMembers->sum('won_count');
        $teamLost          = $teamMembers->sum('lost_count');

        $stages       = ['prospecting', 'proposal', 'negotiation'];
        $stageLabels  = ['Prospecting', 'Proposal', 'Negotiation'];
        $stageColors  = ['#6366f1', '#3b82f6', '#f59e0b'];
        // Per-sales stage breakdown: [{name: ..., totals: {stage: count}}]
        $stageBreakdown = $teamMembers->map(function ($s) use ($stages) {
            $totals = [];
            foreach ($stages as $stage) {
                $totals[$stage] = Opportunity::where('sales_id', $s->id)->where('stage', $stage)->count();
            }
            return ['name' => $s->name, 'totals' => $totals];
        })->all();

        $recentActivities = ActivityLog::latest()->take(10)->get();
        $activityIcons    = [
            'call'    => 'phone',
            'email'   => 'mail',
            'meeting' => 'groups',
            'note'    => 'sticky_note_2',
        ];

        // Chart Data: Revenue Trend (Last 6 Months) for the whole team
        $salesIds = $teamMembers->pluck('id')->toArray();
        $revenueTrend = ['labels' => [], 'data' => []];
        for ($i = 5; $i >= 0; $i--) {
            $m = Carbon::now()->subMonths($i);
            $revenueTrend['labels'][] = $m->format('M Y');
            $revenueTrend['data'][] = Booking::whereIn('sales_id', $salesIds)
                ->whereMonth('created_at', $m->month)
                ->whereYear('created_at', $m->year)
                ->where('status', 'completed')->sum('price');
        }

        return view('dashboard.manager', compact(
            'teamMembers', 'teamPipelineValue', 'teamWon', 'teamLost',
            'stages', 'stageLabels', 'stageColors', 'stageBreakdown',
            'recentActivities', 'activityIcons', 'revenueTrend'
        ));
    }

    /* ------------------------------------------------------------------ */
    /* SALES                                                                */
    /* ------------------------------------------------------------------ */
    public function sales()
    {
        $user  = Auth::user();
        $today = Carbon::today();
        $now   = Carbon::now();
        $weekStart  = $now->copy()->startOfWeek();
        $monthStart = $now->copy()->startOfMonth();
        $yearStart  = $now->copy()->startOfYear();

        $todayRevenue = Booking::where('sales_id', $user->id)->whereDate('created_at', $today)
                            ->where('status', 'completed')->sum('price');
        $weekRevenue  = Booking::where('sales_id', $user->id)->whereBetween('created_at', [$weekStart, $now])
                            ->where('status', 'completed')->sum('price');
        $monthRevenue = Booking::where('sales_id', $user->id)->whereBetween('created_at', [$monthStart, $now])
                            ->where('status', 'completed')->sum('price');
        $yearRevenue  = Booking::where('sales_id', $user->id)->whereBetween('created_at', [$yearStart, $now])
                            ->where('status', 'completed')->sum('price');

        $myClients      = Client::where('assigned_sales_id', $user->id)->count();
        $activeBookings = Booking::where('sales_id', $user->id)->where('status', 'active')->count();
        $recentBookings = Booking::where('sales_id', $user->id)->latest()->take(5)->get();

        // Chart Data: Pipeline Funnel
        $pipelineStages = ['prospecting', 'proposal', 'negotiation', 'won'];
        $salesFunnel = [];
        foreach($pipelineStages as $s) {
            $salesFunnel[] = Opportunity::where('sales_id', $user->id)->where('stage', $s)->count();
        }
        
        // Chart Data: Revenue Trend (Last 6 Months)
        $revenueTrend = ['labels' => [], 'data' => []];
        for ($i = 5; $i >= 0; $i--) {
            $m = Carbon::now()->subMonths($i);
            $revenueTrend['labels'][] = $m->format('M Y');
            $revenueTrend['data'][] = Booking::where('sales_id', $user->id)
                ->whereMonth('created_at', $m->month)
                ->whereYear('created_at', $m->year)
                ->where('status', 'completed')->sum('price');
        }

        return view('dashboard.sales', compact(
            'todayRevenue', 'weekRevenue', 'monthRevenue', 'yearRevenue',
            'myClients', 'activeBookings', 'recentBookings',
            'salesFunnel', 'revenueTrend'
        ));
    }

    /* ------------------------------------------------------------------ */
    /* OPERATIONAL                                                          */
    /* ------------------------------------------------------------------ */
    public function operational()
    {
        $availableFleet   = Vehicle::where('status', 'available')->count();
        $onTripFleet      = Vehicle::where('status', 'on_trip')->count();
        $maintenanceFleet = Vehicle::where('status', 'maintenance')->count();
        $activeBookings   = Booking::where('status', 'active')->count();
        $activeBookingList= Booking::where('status', 'active')->latest()->take(10)->get();

        return view('dashboard.operational', compact(
            'availableFleet', 'onTripFleet', 'maintenanceFleet',
            'activeBookings', 'activeBookingList'
        ));
    }

    /* ------------------------------------------------------------------ */
    /* FINANCE                                                              */
    /* ------------------------------------------------------------------ */
    public function finance()
    {
        $today      = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd   = Carbon::now()->endOfMonth();

        $todayRevenue   = Invoice::whereDate('paid_at', $today)->sum('amount') ?? 0;
        $monthRevenue   = Invoice::whereBetween('paid_at', [$monthStart, $monthEnd])->sum('amount') ?? 0;
        $paidThisMonth  = Invoice::whereBetween('paid_at', [$monthStart, $monthEnd])->count();
        $outstanding    = Invoice::where('status', 'unpaid')->sum('amount') ?? 0;
        $pendingInvoice = Invoice::where('status', 'unpaid')->count();
        $overdueCount   = Invoice::where('status', 'unpaid')->where('due_date', '<', $today)->count();
        $overdueInvoices= Invoice::where('status', 'unpaid')->where('due_date', '<', $today)->latest()->take(10)->get();

        return view('dashboard.finance', compact(
            'todayRevenue', 'monthRevenue', 'paidThisMonth',
            'outstanding', 'pendingInvoice', 'overdueCount', 'overdueInvoices'
        ));
    }

    /* ------------------------------------------------------------------ */
    /* KUSTOMISASI DASHBOARD                                                */
    /* ------------------------------------------------------------------ */
    public function saveLayout(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();
        $user->dashboard_settings = $request->input('layout');
        $user->save();
        return response()->json(['success' => true]);
    }
}
