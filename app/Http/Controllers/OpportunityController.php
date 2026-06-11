<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Opportunity;
use App\Models\Product;
use App\Models\User;
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
                $user->isManager(),
                function ($q) use ($user) {
                    $teamIds = User::where('manager_id', $user->id)->where('role', 'sales')->pluck('id');
                    $q->whereIn('sales_id', $teamIds);
                }
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
            'stage'               => 'in:call_meeting,prospecting,proposal,negotiation,won,lost',
            'products'            => 'nullable|array',
            'products.*.id'       => 'nullable|string',
            'products.*.category' => 'required|string',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.estimatedValue' => 'required|numeric|min:0',
            'products.*.details'  => 'nullable|string',
            'expected_close_date' => 'nullable|date',
            'notes'               => 'nullable|string',
            'subType'             => 'nullable|string',
            'estimated_value'     => 'nullable|numeric|min:0',
        ]);

        // Force sales role to own record; managers/GMs shouldn't be creating them based on spec
        abort_if(! $user->isSales(), 403, 'Hanya Sales yang dapat membuat Opportunity baru.');
        $validated['sales_id'] = $user->id;

        $validated['stage'] = 'call_meeting';

        $estimatedValue = 0;
        $products = [];
        if (!empty($validated['products'])) {
            $products = $validated['products'];
            foreach ($products as $p) {
                $estimatedValue += (float)$p['estimatedValue'] * (int)$p['quantity'];
            }
        } else {
            $estimatedValue = $request->input('estimated_value', 0);
        }

        $validated['estimated_value'] = $estimatedValue;
        $validated['products'] = $products;

        $historyEntry = [
            'id' => 'h' . time() . rand(1000, 9999),
            'stage' => 'call_meeting',
            'subType' => $request->subType,
            'timestamp' => now()->toIso8601String(),
            'note' => $request->notes,
            'products' => $products,
            'estimatedValue' => $estimatedValue,
        ];
        $validated['history_timeline'] = [$historyEntry];

        $opportunity = Opportunity::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'opportunity' => $opportunity]);
        }

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

        $nextStages = $this->pipelineService->getNextStages($opportunity->stage);

        $approvalRequests = $opportunity->approvalRequests()
            ->with(['requester', 'currentApprover'])
            ->latest()
            ->get();

        return view('pipeline.show', compact(
            'opportunity',
            'activityLogs',
            'nextStages',
            'approvalRequests'
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
            'stage'               => 'required|in:call_meeting,prospecting,proposal,negotiation,won,lost',
            'products'            => 'nullable|array',
            'products.*.id'       => 'nullable|string',
            'products.*.category' => 'required|string',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.estimatedValue' => 'required|numeric|min:0',
            'products.*.details'  => 'nullable|string',
            'estimated_value'     => 'nullable|numeric|min:0',
            'final_value'         => 'nullable|numeric|min:0',
            'pax'                 => 'nullable|integer|min:1',
            'expected_close_date' => 'nullable|date',
            'actual_close_date'   => 'nullable|date',
            'lost_reason'         => 'nullable|string',
            'notes'               => 'nullable|string',
            'subType'             => 'nullable|string',
        ]);

        $estimatedValue = 0;
        if (!empty($validated['products'])) {
            foreach ($validated['products'] as $p) {
                $estimatedValue += (float)$p['estimatedValue'] * (int)$p['quantity'];
            }
        } else {
            $estimatedValue = $request->input('estimated_value', $opportunity->estimated_value ?? 0);
        }
        $validated['estimated_value'] = $estimatedValue;

        // Stage change validation
        $oldStage = $opportunity->stage;
        $isStageChanged = $validated['stage'] !== $oldStage;

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            if ($isStageChanged) {
                if (!$this->pipelineService->canTransition($opportunity->stage, $validated['stage'])) {
                    if ($request->wantsJson()) {
                        return response()->json(['ok' => false, 'message' => "Tidak dapat berpindah ke {$validated['stage']}."], 422);
                    }
                    return back()->withErrors([
                        'stage' => "Tidak dapat berpindah dari {$opportunity->stage} ke {$validated['stage']}.",
                    ]);
                }

                // Validasi hak akses khusus untuk role 'sales' yang merupakan pemilik oportunitas
                if (auth()->user()->role !== 'sales') {
                    if ($request->wantsJson()) {
                        return response()->json(['ok' => false, 'message' => 'Akses ditolak: Hanya Sales yang dapat mengubah stage.'], 403);
                    }
                    return back()->withErrors(['stage' => 'Akses ditolak: Hanya Sales yang dapat mengubah stage.']);
                }

                if ($opportunity->sales_id !== auth()->id()) {
                    if ($request->wantsJson()) {
                        return response()->json(['ok' => false, 'message' => 'Akses ditolak: Hanya Sales pemilik oportunitas yang dapat mengubah stage.'], 403);
                    }
                    return back()->withErrors(['stage' => 'Akses ditolak: Hanya Sales pemilik oportunitas yang dapat mengubah stage.']);
                }

                // Database Transaction for Fleet & Driver
                $targetFleetStatus = $validated['stage'] === 'won' ? 'on_trip' : ($validated['stage'] === 'negotiation' ? 'maintenance' : 'available');
                $targetDriverStatus = $validated['stage'] === 'won' ? 'Assigned' : ($validated['stage'] === 'negotiation' ? 'Reserved' : 'Available');

                if ($request->has('fleet_ids')) {
                    $fleetIds = $request->input('fleet_ids') ?: [];
                    $opportunity->assignedVehicles()->whereNotIn('id', $fleetIds)->update([
                        'assigned_opportunity_id' => null,
                        'status' => 'available'
                    ]);

                    if (count($fleetIds) > 0) {
                        $vehiclesToAssign = \App\Models\Vehicle::whereIn('id', $fleetIds)->lockForUpdate()->get();
                        foreach ($vehiclesToAssign as $vehicle) {
                            if ($vehicle->assigned_opportunity_id !== $opportunity->id && $vehicle->status !== 'available') {
                                throw new \Exception("Unit kendaraan {$vehicle->plate_number} sudah dibooking oleh Sales lain.");
                            }
                            $vehicle->update(['assigned_opportunity_id' => $opportunity->id, 'status' => $targetFleetStatus]);
                        }
                    }
                } else if (in_array($validated['stage'], ['lost', 'call_meeting', 'prospecting', 'proposal'])) {
                    $opportunity->assignedVehicles()->update(['assigned_opportunity_id' => null, 'status' => 'available']);
                }

                if ($request->has('driver_ids')) {
                    $driverIds = $request->input('driver_ids') ?: [];
                    $opportunity->assignedDrivers()->whereNotIn('id', $driverIds)->update([
                        'assigned_opportunity_id' => null,
                        'status' => 'Available'
                    ]);

                    if (count($driverIds) > 0) {
                        $driversToAssign = \App\Models\Driver::whereIn('id', $driverIds)->lockForUpdate()->get();
                        foreach ($driversToAssign as $driver) {
                            if ($driver->assigned_opportunity_id !== $opportunity->id && $driver->status !== 'Available') {
                                throw new \Exception("Supir {$driver->name} sudah dibooking oleh Sales lain.");
                            }
                            $driver->update(['assigned_opportunity_id' => $opportunity->id, 'status' => $targetDriverStatus]);
                        }
                    }
                } else if (in_array($validated['stage'], ['lost', 'call_meeting', 'prospecting', 'proposal'])) {
                    $opportunity->assignedDrivers()->update(['assigned_opportunity_id' => null, 'status' => 'Available']);
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
                $validated['final_value'] = $validated['final_value'] ?? $estimatedValue;
                $validated['actual_close_date'] = $validated['actual_close_date'] ?? now()->toDateString();
            }
            $validated['stage_changed_at'] = now();

            $history = $opportunity->history_timeline ?? [];
            $history[] = [
                'id' => 'h' . time() . rand(1000, 9999),
                'stage' => $validated['stage'],
                'subType' => $request->subType,
                'timestamp' => now()->toIso8601String(),
                'note' => $request->notes,
                'products' => $validated['products'] ?? $opportunity->products,
                'estimatedValue' => $estimatedValue,
            ];
            $validated['history_timeline'] = $history;
        } else {
            // Update latest history entry if products/estimatedValue changed
            $history = $opportunity->history_timeline ?? [];
            if (count($history) > 0) {
                $lastIdx = count($history) - 1;
                $history[$lastIdx]['products'] = $validated['products'] ?? $opportunity->products;
                $history[$lastIdx]['estimatedValue'] = $estimatedValue;
                if ($request->notes) {
                    $history[$lastIdx]['note'] = $request->notes;
                }
                $validated['history_timeline'] = $history;
            }
        }

        $opportunity->update($validated);
        
        if ($isStageChanged && $validated['stage'] === 'won') {
            $this->pipelineService->triggerWonActions($opportunity);
        }

        \Illuminate\Support\Facades\DB::commit();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'opportunity' => $opportunity->fresh()]);
        }

        return back()->with('success', 'Opportunity berhasil diperbarui.');
        
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withErrors(['booking' => $e->getMessage()]);
        }
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
        $user = auth()->user();
        if ($user->role !== 'sales' || $opportunity->sales_id !== $user->id) {
            abort(403, 'Akses ditolak: Hanya Sales pemilik yang dapat mengubah stage.');
        }

        $validated = $request->validate([
            'stage'       => 'required|in:call_meeting,prospecting,proposal,negotiation,won,lost',
            'lost_reason' => 'required_if:stage,lost|nullable|string',
            'notes'       => 'nullable|string',
            'fleet_ids'   => 'nullable|array',
            'fleet_ids.*' => 'exists:vehicles,id',
            'driver_ids'  => 'nullable|array',
            'driver_ids.*'=> 'exists:drivers,id',
        ]);

        if (!$this->pipelineService->canTransition($opportunity->stage, $validated['stage'])) {
            return back()->withErrors([
                'stage' => "Transisi dari {$opportunity->stage} ke {$validated['stage']} tidak diizinkan.",
            ]);
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $targetFleetStatus = $validated['stage'] === 'won' ? 'on_trip' : ($validated['stage'] === 'negotiation' ? 'maintenance' /* usually reserved/hold */ : 'available');
            $targetDriverStatus = $validated['stage'] === 'won' ? 'Assigned' : ($validated['stage'] === 'negotiation' ? 'Reserved' : 'Available');

            // Handle Fleet Assignments
            if (isset($validated['fleet_ids'])) {
                $fleetIds = $validated['fleet_ids'];
                // Release old fleets
                $opportunity->assignedVehicles()->whereNotIn('id', $fleetIds)->update([
                    'assigned_opportunity_id' => null,
                    'status' => 'available'
                ]);

                // Assign new fleets
                if (count($fleetIds) > 0) {
                    $vehiclesToAssign = \App\Models\Vehicle::whereIn('id', $fleetIds)
                        ->lockForUpdate()
                        ->get();
                    
                    foreach ($vehiclesToAssign as $vehicle) {
                        if ($vehicle->assigned_opportunity_id !== $opportunity->id && $vehicle->status !== 'available') {
                            throw new \Exception("Unit kendaraan {$vehicle->plate_number} sudah dibooking oleh Sales lain.");
                        }
                        $vehicle->update([
                            'assigned_opportunity_id' => $opportunity->id,
                            'status' => $targetFleetStatus
                        ]);
                    }
                }
            } else if (in_array($validated['stage'], ['lost', 'call_meeting', 'prospecting', 'proposal'])) {
                 $opportunity->assignedVehicles()->update([
                    'assigned_opportunity_id' => null,
                    'status' => 'available'
                ]);
            }

            // Handle Driver Assignments
            if (isset($validated['driver_ids'])) {
                $driverIds = $validated['driver_ids'];
                // Release old drivers
                $opportunity->assignedDrivers()->whereNotIn('id', $driverIds)->update([
                    'assigned_opportunity_id' => null,
                    'status' => 'Available'
                ]);

                // Assign new drivers
                if (count($driverIds) > 0) {
                    $driversToAssign = \App\Models\Driver::whereIn('id', $driverIds)
                        ->lockForUpdate()
                        ->get();
                    
                    foreach ($driversToAssign as $driver) {
                        if ($driver->assigned_opportunity_id !== $opportunity->id && $driver->status !== 'Available') {
                            throw new \Exception("Supir {$driver->name} sudah dibooking oleh Sales lain.");
                        }
                        $driver->update([
                            'assigned_opportunity_id' => $opportunity->id,
                            'status' => $targetDriverStatus
                        ]);
                    }
                }
            } else if (in_array($validated['stage'], ['lost', 'call_meeting', 'prospecting', 'proposal'])) {
                 $opportunity->assignedDrivers()->update([
                    'assigned_opportunity_id' => null,
                    'status' => 'Available'
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

            $updates = [
                'stage' => $validated['stage'],
                'stage_changed_at' => now(),
            ];

            if ($validated['stage'] === 'lost' && !empty($validated['lost_reason'])) {
                $updates['lost_reason']        = $validated['lost_reason'];
                $updates['actual_close_date']  = now()->toDateString();
            }

            if ($validated['stage'] === 'won') {
                $updates['actual_close_date']  = now()->toDateString();
                $opportunity->update($updates);
                $this->pipelineService->triggerWonActions($opportunity->fresh());
                
                \Illuminate\Support\Facades\DB::commit();
                return back()->with('success', 'Selamat! Opportunity berhasil dimenangkan dan Unit Operasional berhasil dialokasikan.');
            }

            $opportunity->update($updates);

            \Illuminate\Support\Facades\DB::commit();
            return back()->with('success', "Stage berhasil diubah ke {$validated['stage']}.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->withErrors(['booking' => $e->getMessage()]);
        }
    }


    // ------------------------------------------------------------------
    // Kanban drag-drop: move card to new stage (PATCH, JSON)
    // ------------------------------------------------------------------

    public function moveStage(Request $request, Opportunity $opportunity)
    {
        $user = auth()->user();
        if ($user->role !== 'sales' || $opportunity->sales_id !== $user->id) {
            return response()->json([
                'ok'      => false,
                'message' => 'Akses ditolak: Hanya Sales pemilik yang dapat mengubah stage.',
            ], 403);
        }

        $validated = $request->validate([
            'stage'           => 'required|in:call_meeting,prospecting,proposal,negotiation,won,lost',
            'lost_reason'     => 'nullable|string|max:500',
            'estimated_value' => 'nullable|numeric|min:0',
            'notes'           => 'nullable|string',
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

        $updates = [
            'stage' => $toStage,
            'stage_changed_at' => now(),
        ];

        if (isset($validated['estimated_value'])) {
            $updates['estimated_value'] = $validated['estimated_value'];
        }

        if ($toStage === 'lost') {
            $updates['lost_reason']       = $validated['lost_reason'] ?? 'Dipindah via Kanban';
            $updates['actual_close_date'] = now()->toDateString();
        }

        if ($toStage === 'won') {
            $updates['actual_close_date'] = now()->toDateString();
        }

        $opportunity->update($updates);

        $activityNotes = "Dipindah via drag-drop kanban";
        if (!empty($validated['notes'])) {
            $activityNotes = $validated['notes'];
        }

        ActivityLog::create([
            'sales_id'       => auth()->id(),
            'client_id'      => $opportunity->client_id,
            'opportunity_id' => $opportunity->id,
            'type'           => 'follow_up',
            'subject'        => "Kanban: {$fromStage} → {$toStage}",
            'notes'          => $activityNotes,
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
            'opportunity' => [
                'estimated_value' => $opportunity->estimated_value
            ]
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
    // Get History Timeline
    // ------------------------------------------------------------------

    public function getHistory(Opportunity $opportunity)
    {
        $this->authorizeView($opportunity);

        return response()->json([
            'history_timeline' => $opportunity->history_timeline ?? []
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

        // GM has full edit access
        if ($user->isGM()) {
            return;
        }

        // Manager can edit if the opportunity belongs to a subordinate sales rep
        if ($user->isManager()) {
            $opportunityOwner = User::find($opportunity->sales_id);
            if ($opportunityOwner && $opportunityOwner->manager_id === $user->id) {
                return;
            }
            abort(403, 'Akses ditolak: Hanya Manager dari Sales pemilik yang dapat mengedit.');
        }

        // Sales can only edit if they are the owner
        if ($user->isSales() && $opportunity->sales_id === $user->id) {
            return;
        }

        abort(403, 'Akses ditolak: Anda tidak memiliki wewenang untuk mengedit oportunitas ini.');
    }
}
