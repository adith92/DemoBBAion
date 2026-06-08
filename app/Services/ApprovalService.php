<?php

namespace App\Services;

use App\Models\ApprovalRequest;
use App\Models\Opportunity;
use App\Models\User;

class ApprovalService
{
    /**
     * Determine the starting approval level based on deal value first, then discount.
     *
     * Director dihapus — GM (level 2) kini approver tertinggi.
     * Rules:
     *   - dealValue > 50,000,000  → level 2 (gm)  [termasuk deal besar >200jt]
     *   - selain itu              → level 1 (manager)
     */
    public static function determineStartingLevel(float $discountPercent, float $dealValue): int
    {
        if ($dealValue > 50_000_000) {
            return 2;
        }

        return 1;
    }

    /**
     * Approval is needed whenever any positive discount is applied.
     */
    public static function needsApproval(float $discountPercent): bool
    {
        return $discountPercent > 0;
    }

    /**
     * Return the first available user for the given approval level.
     *
     * Level 1 → manager
     * Level 2 → gm (approver tertinggi; director dihapus)
     */
    public static function getApproverForLevel(int $level): ?User
    {
        $roleMap = [
            1 => 'manager',
            2 => 'gm',
        ];

        $role = $roleMap[$level] ?? 'gm';

        return User::where('role', $role)->first();
    }

    /**
     * Determine the maximum level needed for the given discount percentage.
     *
     * Director dihapus — max level dibatasi 2 (GM).
     * discount ≤ 5%   → max level 1 (manager)
     * discount > 5%   → max level 2 (gm)
     */
    public static function determineMaxLevel(float $discountPercent): int
    {
        if ($discountPercent > 5) {
            return 2;
        }

        return 1;
    }

    /**
     * Create the first approval request in the chain for an opportunity.
     *
     * Determines starting level, locates appropriate approver, persists record.
     */
    public static function createApprovalChain(Opportunity $opp, float $discountPercent): ApprovalRequest
    {
        $dealValue    = (float) ($opp->estimated_value ?? $opp->final_value ?? 0);
        $startLevel   = static::determineStartingLevel($discountPercent, $dealValue);

        // If starting level is forced high by deal value, still cap at max level for discount
        $maxLevel     = static::determineMaxLevel($discountPercent);
        $level        = max($startLevel, 1);

        $approver     = static::getApproverForLevel($level);

        // Escalate if no approver at current level
        while ($approver === null && $level < 2) {
            $level++;
            $approver = static::getApproverForLevel($level);
        }

        $originalPrice = (float) ($opp->estimated_value ?? 0);
        $finalPrice    = $originalPrice * (1 - ($discountPercent / 100));

        return ApprovalRequest::create([
            'opportunity_id'     => $opp->id,
            'requested_by'       => $opp->sales_id,
            'current_approver_id'=> $approver?->id,
            'type'               => 'discount',
            'discount_percent'   => $discountPercent,
            'original_price'     => $originalPrice,
            'final_price'        => $finalPrice,
            'level'              => $level,
            'status'             => 'pending',
            'notes'              => null,
            'rejection_reason'   => null,
            'approved_at'        => null,
            'rejected_at'        => null,
        ]);
    }

    /**
     * Approve the current approval request.
     *
     * If additional levels are required (based on discount %), a new
     * ApprovalRequest is created for the next level. Otherwise, the
     * opportunity is marked as discount-approved.
     */
    public static function approve(ApprovalRequest $req, User $approver, ?string $notes = null): void
    {
        $req->update([
            'status'      => 'approved',
            'notes'       => $notes,
            'approved_at' => now(),
        ]);

        $discountPercent = (float) $req->discount_percent;
        $maxLevel        = static::determineMaxLevel($discountPercent);
        $currentLevel    = (int) $req->level;

        if ($currentLevel < $maxLevel) {
            // Create next-level approval request
            $nextLevel    = $currentLevel + 1;
            $nextApprover = static::getApproverForLevel($nextLevel);

            // Escalate if no approver found at next level
            while ($nextApprover === null && $nextLevel < 2) {
                $nextLevel++;
                $nextApprover = static::getApproverForLevel($nextLevel);
            }

            ApprovalRequest::create([
                'opportunity_id'     => $req->opportunity_id,
                'requested_by'       => $req->requested_by,
                'current_approver_id'=> $nextApprover?->id,
                'type'               => $req->type,
                'discount_percent'   => $req->discount_percent,
                'original_price'     => $req->original_price,
                'final_price'        => $req->final_price,
                'level'              => $nextLevel,
                'status'             => 'pending',
                'notes'              => null,
                'rejection_reason'   => null,
                'approved_at'        => null,
                'rejected_at'        => null,
            ]);
        } else {
            // All levels approved – mark opportunity
            $opp = $req->opportunity;
            $opp->update([
                'discount_approved' => true,
                'approved_by'       => $approver->id,
            ]);
        }
    }

    /**
     * Reject the current approval request.
     */
    public static function reject(ApprovalRequest $req, User $approver, string $reason): void
    {
        $req->update([
            'status'           => 'rejected',
            'rejection_reason' => $reason,
            'rejected_at'      => now(),
        ]);
    }
}
