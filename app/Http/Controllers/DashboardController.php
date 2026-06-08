<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Client;
use App\Models\PurchaseOrder;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\Opportunity;
use App\Models\ApprovalRequest;
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

        $pendingPO          = PurchaseOrder::where('status', 'pending')->count();
        $availableVehicles  = Vehicle::where('status', 'available')->count();
        $pendingDispatch    = Booking::where('status', 'pending')->count();
        $upcomingMeetings   = 0;

        $totalMonthlyRevenue = Booking::whereBetween('created_at', [$monthStart, $monthEnd])
                                  ->where('status', 'completed')->sum('price');

        $completedBookings   = Booking::whereMonth('created_at', Carbon::now()->month)
                                  ->where('status', 'completed')->count();

        $avgRevenuePerBooking = $completedBookings > 0
                                ? $totalMonthlyRevenue / $completedBookings
                                : 0;

        $activeClients = Client::where('status', 'active')->count();
        $todayRevenue  = Booking::whereDate('created_at', $today)
                            ->where('status', 'completed')->sum('price');

        return view('dashboard.gm', compact(
            'pendingPO', 'availableVehicles', 'pendingDispatch', 'upcomingMeetings',
            'totalMonthlyRevenue', 'completedBookings', 'avgRevenuePerBooking',
            'activeClients', 'todayRevenue'
        ));
    }

    /* ------------------------------------------------------------------ */
    /* MANAGER                                                              */
    /* ------------------------------------------------------------------ */
    public function manager()
    {
        $now        = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();

        $teamMembers = User::where('role', 'sales')->get()->map(function ($s) use ($now) {
            $won   = Opportunity::where('sales_id', $s->id)->whereMonth('updated_at', $now->month)->where('stage', 'won')->count();
            $lost  = Opportunity::where('sales_id', $s->id)->whereMonth('updated_at', $now->month)->where('stage', 'lost')->count();
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

        $pendingApprovals = ApprovalRequest::where('status', 'pending')->count();
        $approvalQueue    = ApprovalRequest::where('status', 'pending')->latest()->take(5)->get();

        $recentActivities = ActivityLog::latest()->take(10)->get();
        $activityIcons    = [
            'call'    => 'phone',
            'email'   => 'mail',
            'meeting' => 'groups',
            'note'    => 'sticky_note_2',
        ];

        return view('dashboard.manager', compact(
            'teamMembers', 'teamPipelineValue', 'teamWon', 'teamLost',
            'stages', 'stageLabels', 'stageColors', 'stageBreakdown',
            'pendingApprovals', 'approvalQueue',
            'recentActivities', 'activityIcons'
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

        return view('dashboard.sales', compact(
            'todayRevenue', 'weekRevenue', 'monthRevenue', 'yearRevenue',
            'myClients', 'activeBookings', 'recentBookings'
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
}
