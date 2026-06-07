<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = ActivityLog::with(['sales', 'client', 'opportunity']);

        // Scope by role
        if ($user->isSales()) {
            $query->where('sales_id', $user->id);
        } elseif ($user->isManager()) {
            // Manager sees own team
            $subordinateIds = User::where('manager_id', $user->id)->pluck('id')->push($user->id);
            $query->whereIn('sales_id', $subordinateIds);
        }
        // director, gm see all

        // Filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('opportunity_id')) {
            $query->where('opportunity_id', $request->opportunity_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('activity_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('activity_date', '<=', $request->date_to);
        }

        // Filter by sales user (manager/gm/director only)
        if ($request->filled('sales_id') && !$user->isSales()) {
            $query->where('sales_id', $request->sales_id);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'newest');
        $activities = match ($sortBy) {
            'oldest'  => $query->orderBy('activity_date')->paginate(20)->withQueryString(),
            'type'    => $query->orderBy('type')->orderByDesc('activity_date')->paginate(20)->withQueryString(),
            'sales'   => $query->orderBy(
                             \App\Models\User::select('name')
                                 ->whereColumn('users.id', 'activity_logs.sales_id')
                                 ->limit(1)
                         )->paginate(20)->withQueryString(),
            default   => $query->orderByDesc('activity_date')->paginate(20)->withQueryString(),
        };

        // Upcoming follow-ups (next_action_date within 7 days)
        $upcomingQuery = ActivityLog::whereNotNull('next_action_date')
            ->whereBetween('next_action_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->with(['client', 'opportunity']);

        if ($user->isSales()) {
            $upcomingQuery->where('sales_id', $user->id);
        } elseif ($user->isManager()) {
            $subordinateIds = User::where('manager_id', $user->id)->pluck('id')->push($user->id);
            $upcomingQuery->whereIn('sales_id', $subordinateIds);
        }

        $upcomingFollowUps = $upcomingQuery->orderBy('next_action_date')->get();

        $clients = Client::orderBy('company_name')->get();

        // Sales users for filter dropdown (manager/gm/director)
        $salesUsers = collect();
        if (!$user->isSales()) {
            if ($user->isManager()) {
                $salesUsers = User::where('manager_id', $user->id)->where('role', 'sales')->orderBy('name')->get();
            } else {
                $salesUsers = User::whereIn('role', ['sales', 'manager'])->orderBy('name')->get();
            }
        }

        return view('activities.index', compact('activities', 'upcomingFollowUps', 'clients', 'salesUsers', 'sortBy'));
    }

    public function create(Request $request)
    {
        $opportunity = null;
        $client = null;

        if ($request->filled('opportunity_id')) {
            $opportunity = Opportunity::with('client')->find($request->opportunity_id);
        }

        if ($request->filled('client_id')) {
            $client = Client::find($request->client_id);
        }

        $clients = Client::orderBy('company_name')->get();
        $opportunities = Opportunity::with('client')
            ->when(auth()->user()->isSales(), fn($q) => $q->where('sales_id', auth()->id()))
            ->orderByDesc('created_at')
            ->get();

        return view('activities.create', compact('clients', 'opportunities', 'opportunity', 'client'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'             => 'required|in:meeting,call,visit,follow_up,email,demo',
            'subject'          => 'required|string|max:255',
            'activity_date'    => 'required|date',
            'client_id'        => 'nullable|exists:clients,id',
            'opportunity_id'   => 'nullable|exists:opportunities,id',
            'duration_minutes' => 'nullable|integer|min:1',
            'outcome'          => 'nullable|string',
            'next_action'      => 'nullable|string|max:255',
            'next_action_date' => 'nullable|date',
            'notes'            => 'nullable|string',
        ]);

        $validated['sales_id'] = auth()->id();

        $activity = ActivityLog::create($validated);

        // Redirect to opportunity if came from one
        if ($activity->opportunity_id) {
            return redirect()
                ->route('opportunities.show', $activity->opportunity_id)
                ->with('success', 'Aktivitas berhasil dicatat.');
        }

        return redirect()
            ->route('activities.index')
            ->with('success', 'Aktivitas berhasil dicatat.');
    }

    public function destroy(ActivityLog $activityLog)
    {
        // Only own activities
        if ($activityLog->sales_id !== auth()->id()) {
            abort(403, 'Anda hanya bisa menghapus aktivitas sendiri.');
        }

        // Only if today
        if (!$activityLog->created_at->isToday()) {
            abort(403, 'Hanya aktivitas hari ini yang dapat dihapus.');
        }

        $activityLog->delete();

        return redirect()
            ->route('activities.index')
            ->with('success', 'Aktivitas berhasil dihapus.');
    }

    /**
     * GET /api/activities/upcoming
     * Returns activities with next_action_date within 7 days for the current sales user.
     */
    public function apiUpcoming(Request $request)
    {
        $user = auth()->user();

        $query = ActivityLog::whereNotNull('next_action_date')
            ->whereBetween('next_action_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->with(['client:id,company_name', 'opportunity:id,title,opp_number'])
            ->orderBy('next_action_date');

        if ($user->isSales()) {
            $query->where('sales_id', $user->id);
        } elseif ($user->isManager()) {
            $subordinateIds = User::where('manager_id', $user->id)->pluck('id')->push($user->id);
            $query->whereIn('sales_id', $subordinateIds);
        }

        $activities = $query->get()->map(function ($a) {
            return [
                'id'               => $a->id,
                'type'             => $a->type,
                'subject'          => $a->subject,
                'next_action'      => $a->next_action,
                'next_action_date' => $a->next_action_date?->toDateString(),
                'client'           => $a->client ? ['id' => $a->client->id, 'name' => $a->client->company_name] : null,
                'opportunity'      => $a->opportunity ? ['id' => $a->opportunity->id, 'title' => $a->opportunity->title, 'opp_number' => $a->opportunity->opp_number] : null,
            ];
        });

        return response()->json(['data' => $activities]);
    }
}
