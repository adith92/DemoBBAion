<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRequest;
use App\Services\ApprovalService;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:gm,manager,sales');
    }

    /**
     * Display the approval queue for the authenticated user.
     *
     * - manager   → level-1 pending approvals assigned to them
     * - gm        → level-2 pending approvals assigned to them
     * - director  → level-3 pending approvals assigned to them
     * - sales     → their own submitted approval requests
     */
    public function index()
    {
        $user = auth()->user();

        $pendingApprovals = collect();
        $myRequests       = collect();

        if ($user->isManager()) {
            $pendingApprovals = ApprovalRequest::with(['opportunity.client', 'opportunity.sales', 'requester'])
                ->where('level', 1)
                ->where('status', 'pending')
                ->where('current_approver_id', $user->id)
                ->orderByDesc('created_at')
                ->paginate(15, ['*'], 'approvals_page');
        } elseif ($user->isGM()) {
            $pendingApprovals = ApprovalRequest::with(['opportunity.client', 'opportunity.sales', 'requester'])
                ->where('level', 2)
                ->where('status', 'pending')
                ->where('current_approver_id', $user->id)
                ->orderByDesc('created_at')
                ->paginate(15, ['*'], 'approvals_page');
        } elseif ($user->isDirector()) {
            $pendingApprovals = ApprovalRequest::with(['opportunity.client', 'opportunity.sales', 'requester'])
                ->where('level', 3)
                ->where('status', 'pending')
                ->where('current_approver_id', $user->id)
                ->orderByDesc('created_at')
                ->paginate(15, ['*'], 'approvals_page');
        }

        if ($user->isSales() || $user->isManager()) {
            $myRequests = ApprovalRequest::with(['opportunity.client', 'currentApprover'])
                ->where('requested_by', $user->id)
                ->orderByDesc('created_at')
                ->paginate(15, ['*'], 'my_page');
        }

        return view('approvals.index', compact('pendingApprovals', 'myRequests', 'user'));
    }

    /**
     * Display the detail page for a single approval request.
     */
    public function show(ApprovalRequest $approval)
    {
        $approval->load(['opportunity.client', 'opportunity.sales', 'opportunity.product', 'requester', 'currentApprover']);

        // Load all requests in the same chain for the chain visualization
        $chainRequests = ApprovalRequest::where('opportunity_id', $approval->opportunity_id)
            ->orderBy('level')
            ->get();

        $maxLevel = ApprovalService::determineMaxLevel((float) $approval->discount_percent);

        return view('approvals.show', compact('approval', 'chainRequests', 'maxLevel'));
    }

    /**
     * Approve an approval request.
     */
    public function approve(Request $request, ApprovalRequest $approval)
    {
        $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = auth()->user();

        ApprovalService::approve($approval, $user, $request->input('notes'));

        return redirect()->route('approvals.index')
            ->with('success', 'Permintaan persetujuan berhasil disetujui.');
    }

    /**
     * Reject an approval request.
     */
    public function reject(Request $request, ApprovalRequest $approval)
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $user = auth()->user();

        ApprovalService::reject($approval, $user, $request->input('rejection_reason'));

        return redirect()->route('approvals.index')
            ->with('error', 'Permintaan persetujuan ditolak.');
    }
}
