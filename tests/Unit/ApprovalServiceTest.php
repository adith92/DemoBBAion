<?php

namespace Tests\Unit;

use App\Services\ApprovalService;
use PHPUnit\Framework\TestCase;

class ApprovalServiceTest extends TestCase
{
    // -------------------------------------------------------------------------
    // determineStartingLevel
    // -------------------------------------------------------------------------

    /**
     * 3% discount, 10jt value → deal is not large enough to skip level 1.
     * Expect starting level = 1 (manager).
     */
    public function test_determine_level_small_discount(): void
    {
        $level = ApprovalService::determineStartingLevel(3.0, 10_000_000);
        $this->assertSame(1, $level);
    }

    /**
     * 5% discount, 60jt value → deal value > 50jt, so skip manager.
     * Expect starting level = 2 (gm).
     */
    public function test_determine_level_large_deal_60jt(): void
    {
        $level = ApprovalService::determineStartingLevel(5.0, 60_000_000);
        $this->assertSame(2, $level);
    }

    /**
     * 10% discount, 200jt value → deal value > 50jt threshold.
     * Director dihapus — deal besar berhenti di GM (level 2).
     */
    public function test_determine_level_very_large_deal_200jt(): void
    {
        $level = ApprovalService::determineStartingLevel(10.0, 200_000_001);
        $this->assertSame(2, $level);
    }

    // -------------------------------------------------------------------------
    // needsApproval
    // -------------------------------------------------------------------------

    /**
     * 0% discount requires no approval.
     */
    public function test_needs_approval_returns_false_for_zero(): void
    {
        $this->assertFalse(ApprovalService::needsApproval(0.0));
    }

    /**
     * Any positive discount requires approval.
     */
    public function test_needs_approval_returns_true_for_any_discount(): void
    {
        $this->assertTrue(ApprovalService::needsApproval(1.0));
        $this->assertTrue(ApprovalService::needsApproval(0.01));
        $this->assertTrue(ApprovalService::needsApproval(50.0));
    }
}
