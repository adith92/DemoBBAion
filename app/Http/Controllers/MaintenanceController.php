<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceLog;
use App\Models\Vehicle;
use App\Models\PurchaseOrder;

class MaintenanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();

        if ($user->isSales() || $user->isFinance()) {
            abort(403, 'Unauthorized');
        }

        // Antrian = scheduled + in_progress (belum selesai)
        $antrian = MaintenanceLog::with('vehicle')
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->when(request('vehicle_id'), fn($q, $v) => $q->where('vehicle_id', $v))
            ->orderBy('scheduled_date')
            ->get();

        // Selesai = completed
        $selesai = MaintenanceLog::with('vehicle')
            ->where('status', 'completed')
            ->when(request('vehicle_id'), fn($q, $v) => $q->where('vehicle_id', $v))
            ->orderBy('completed_date', 'desc')
            ->paginate(15, ['*'], 'selesai_page');

        // Legacy $logs for any view that still uses it
        $activeTab = request('tab', 'antrian');
        $logs = $activeTab === 'selesai' ? $selesai : $antrian;

        $stats = [
            'scheduled'   => MaintenanceLog::where('status', 'scheduled')->count(),
            'in_progress' => MaintenanceLog::where('status', 'in_progress')->count(),
            'completed'   => MaintenanceLog::where('status', 'completed')->count(),
            'total_cost'  => MaintenanceLog::where('status', 'completed')->sum('cost'),
        ];

        $vehicles    = Vehicle::orderBy('brand')->get(['id', 'plate_number', 'brand', 'model']);
        $upcomingPOs = PurchaseOrder::where('status', 'pending')->orderBy('created_at', 'desc')->limit(5)->get();

        return view('maintenance.index', compact('logs', 'antrian', 'selesai', 'stats', 'upcomingPOs', 'vehicles', 'activeTab'));
    }
}
