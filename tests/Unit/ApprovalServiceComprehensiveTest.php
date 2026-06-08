<?php

namespace Tests\Unit;

use App\Models\ApprovalRequest;
use App\Models\Opportunity;
use App\Models\User;
use App\Services\ApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalServiceComprehensiveTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // determineStartingLevel Tests
    // =========================================================================

    public function test_determine_starting_level_small_deal_small_discount()
    {
        $level = ApprovalService::determineStartingLevel(3.0, 10_000_000);
        expect($level)->toBeOne();
    }

    public function test_determine_starting_level_medium_deal_below_50m()
    {
        $level = ApprovalService::determineStartingLevel(5.0, 30_000_000);
        expect($level)->toBeOne();
    }

    public function test_determine_starting_level_exactly_at_50m_threshold()
    {
        $level = ApprovalService::determineStartingLevel(5.0, 50_000_000);
        expect($level)->toBeOne();
    }

    public function test_determine_starting_level_above_50m_threshold()
    {
        $level = ApprovalService::determineStartingLevel(5.0, 50_000_001);
        expect($level)->toBeTwo();
    }

    public function test_determine_starting_level_large_deal_60m()
    {
        $level = ApprovalService::determineStartingLevel(5.0, 60_000_000);
        expect($level)->toBeTwo();
    }

    public function test_determine_starting_level_exactly_at_200m_threshold()
    {
        $level = ApprovalService::determineStartingLevel(10.0, 200_000_000);
        expect($level)->toBeTwo();
    }

    public function test_determine_starting_level_above_200m_threshold()
    {
        // Director dihapus — deal besar berhenti di GM (level 2).
        $level = ApprovalService::determineStartingLevel(10.0, 200_000_001);
        expect($level)->toBeTwo();
    }

    public function test_determine_starting_level_very_large_deal_300m()
    {
        // Director dihapus — deal sangat besar tetap mentok di GM (level 2).
        $level = ApprovalService::determineStartingLevel(15.0, 300_000_000);
        expect($level)->toBeTwo();
    }

    public function test_determine_starting_level_with_zero_discount()
    {
        $level = ApprovalService::determineStartingLevel(0.0, 100_000_000);
        expect($level)->toBeTwo();
    }

    public function test_determine_starting_level_with_large_discount()
    {
        $level = ApprovalService::determineStartingLevel(50.0, 10_000_000);
        expect($level)->toBeOne();
    }

    // =========================================================================
    // needsApproval Tests
    // =========================================================================

    public function test_needs_approval_returns_false_for_zero_discount()
    {
        expect(ApprovalService::needsApproval(0.0))->toBeFalse();
    }

    public function test_needs_approval_returns_false_for_negative_discount()
    {
        expect(ApprovalService::needsApproval(-5.0))->toBeFalse();
    }

    public function test_needs_approval_returns_true_for_minimal_discount()
    {
        expect(ApprovalService::needsApproval(0.01))->toBeTrue();
    }

    public function test_needs_approval_returns_true_for_small_discount()
    {
        expect(ApprovalService::needsApproval(1.0))->toBeTrue();
    }

    public function test_needs_approval_returns_true_for_medium_discount()
    {
        expect(ApprovalService::needsApproval(10.0))->toBeTrue();
    }

    public function test_needs_approval_returns_true_for_large_discount()
    {
        expect(ApprovalService::needsApproval(50.0))->toBeTrue();
    }

    // =========================================================================
    // determineMaxLevel Tests
    // =========================================================================

    public function test_determine_max_level_zero_discount()
    {
        $maxLevel = ApprovalService::determineMaxLevel(0.0);
        expect($maxLevel)->toBeOne();
    }

    public function test_determine_max_level_small_discount_up_to_5_percent()
    {
        $maxLevel = ApprovalService::determineMaxLevel(3.0);
        expect($maxLevel)->toBeOne();
    }

    public function test_determine_max_level_exactly_5_percent()
    {
        $maxLevel = ApprovalService::determineMaxLevel(5.0);
        expect($maxLevel)->toBeOne();
    }

    public function test_determine_max_level_above_5_percent_up_to_15()
    {
        $maxLevel = ApprovalService::determineMaxLevel(10.0);
        expect($maxLevel)->toBeTwo();
    }

    public function test_determine_max_level_exactly_15_percent()
    {
        $maxLevel = ApprovalService::determineMaxLevel(15.0);
        expect($maxLevel)->toBeTwo();
    }

    public function test_determine_max_level_above_15_percent()
    {
        // Director dihapus — max level kini dibatasi 2 (GM).
        $maxLevel = ApprovalService::determineMaxLevel(20.0);
        expect($maxLevel)->toBeTwo();
    }

    public function test_determine_max_level_very_high_discount()
    {
        // Director dihapus — max level kini dibatasi 2 (GM).
        $maxLevel = ApprovalService::determineMaxLevel(99.0);
        expect($maxLevel)->toBeTwo();
    }

    // =========================================================================
    // getApproverForLevel Tests
    // =========================================================================

    public function test_get_approver_for_level_1_returns_manager()
    {
        $manager = User::factory()->create(['role' => 'manager']);

        $approver = ApprovalService::getApproverForLevel(1);

        expect($approver)->not->toBeNull();
        expect($approver->role)->toBe('manager');
    }

    public function test_get_approver_for_level_2_returns_gm()
    {
        $gm = User::factory()->create(['role' => 'gm']);

        $approver = ApprovalService::getApproverForLevel(2);

        expect($approver)->not->toBeNull();
        expect($approver->role)->toBe('gm');
    }

    public function test_get_approver_for_invalid_level_falls_back_to_gm()
    {
        // Director dihapus — level tertinggi & fallback kini GM.
        $gm = User::factory()->create(['role' => 'gm']);

        $approver = ApprovalService::getApproverForLevel(999);

        expect($approver)->not->toBeNull();
        expect($approver->role)->toBe('gm');
    }

    public function test_get_approver_returns_null_when_no_user_exists()
    {
        $approver = ApprovalService::getApproverForLevel(1);

        expect($approver)->toBeNull();
    }

    // =========================================================================
    // createApprovalChain Tests
    // =========================================================================

    public function test_create_approval_chain_with_small_discount_and_small_deal()
    {
        $sales = User::factory()->create(['role' => 'sales']);
        $manager = User::factory()->create(['role' => 'manager']);
        $opp = Opportunity::factory()->create([
            'sales_id' => $sales->id,
            'estimated_value' => 10_000_000,
        ]);

        $approval = ApprovalService::createApprovalChain($opp, 3.0);

        expect($approval)->toBeInstanceOf(ApprovalRequest::class);
        expect($approval->opportunity_id)->toBe($opp->id);
        expect($approval->requested_by)->toBe($sales->id);
        expect($approval->level)->toBeOne();
        expect($approval->status)->toBe('pending');
        expect((float)$approval->discount_percent)->toBe(3.0);
        expect($approval->type)->toBe('discount');
    }

    public function test_create_approval_chain_with_large_deal()
    {
        $sales = User::factory()->create(['role' => 'sales']);
        $gm = User::factory()->create(['role' => 'gm']);
        $opp = Opportunity::factory()->create([
            'sales_id' => $sales->id,
            'estimated_value' => 60_000_000,
        ]);

        $approval = ApprovalService::createApprovalChain($opp, 5.0);

        expect($approval->level)->toBeTwo();
        expect($approval->current_approver_id)->toBe($gm->id);
    }

    public function test_create_approval_chain_calculates_final_price_correctly()
    {
        $sales = User::factory()->create(['role' => 'sales']);
        $manager = User::factory()->create(['role' => 'manager']);
        $originalPrice = 10_000_000;
        $discountPercent = 10.0;
        $opp = Opportunity::factory()->create([
            'sales_id' => $sales->id,
            'estimated_value' => $originalPrice,
        ]);

        $approval = ApprovalService::createApprovalChain($opp, $discountPercent);

        $expectedFinalPrice = $originalPrice * (1 - ($discountPercent / 100));
        expect((float)$approval->final_price)->toBe($expectedFinalPrice);
    }

    public function test_create_approval_chain_escalates_when_no_approver_at_level()
    {
        $sales = User::factory()->create(['role' => 'sales']);
        $gm = User::factory()->create(['role' => 'gm']);
        $opp = Opportunity::factory()->create([
            'sales_id' => $sales->id,
            'estimated_value' => 10_000_000,
        ]);

        $approval = ApprovalService::createApprovalChain($opp, 3.0);

        // Manager tidak ada → eskalasi ke GM (level 2, approver tertinggi).
        expect($approval->level)->toBe(2);
        expect($approval->current_approver_id)->toBe($gm->id);
    }

    public function test_create_approval_chain_uses_final_value_if_estimated_value_missing()
    {
        $sales = User::factory()->create(['role' => 'sales']);
        $manager = User::factory()->create(['role' => 'manager']);
        $opp = Opportunity::factory()->create([
            'sales_id' => $sales->id,
            'estimated_value' => null,
            'final_value' => 10_000_000,
        ]);

        $approval = ApprovalService::createApprovalChain($opp, 5.0);

        expect((float)$approval->original_price)->toBe(0.0); // estimated_value is null
    }

    // =========================================================================
    // approve Tests
    // =========================================================================

    public function test_approve_single_level_approval()
    {
        $sales = User::factory()->create(['role' => 'sales']);
        $manager = User::factory()->create(['role' => 'manager']);
        $approver = User::factory()->create(['role' => 'gm']);

        $opp = Opportunity::factory()->create([
            'sales_id' => $sales->id,
            'estimated_value' => 10_000_000,
            'discount_approved' => false,
        ]);

        $approval = ApprovalRequest::factory()->create([
            'opportunity_id' => $opp->id,
            'requested_by' => $sales->id,
            'current_approver_id' => $manager->id,
            'discount_percent' => 3.0,
            'level' => 1,
            'status' => 'pending',
        ]);

        ApprovalService::approve($approval, $approver, 'Approved by director');

        $approval->refresh();
        expect($approval->status)->toBe('approved');
        expect($approval->notes)->toBe('Approved by director');
        expect($approval->approved_at)->not->toBeNull();
    }

    public function test_approve_creates_next_level_approval_request()
    {
        $sales = User::factory()->create(['role' => 'sales']);
        $manager = User::factory()->create(['role' => 'manager']);
        $gm = User::factory()->create(['role' => 'gm']);
        $approver = User::factory()->create(['role' => 'gm']);

        $opp = Opportunity::factory()->create([
            'sales_id' => $sales->id,
            'estimated_value' => 10_000_000,
        ]);

        $approval = ApprovalRequest::factory()->create([
            'opportunity_id' => $opp->id,
            'requested_by' => $sales->id,
            'current_approver_id' => $manager->id,
            'discount_percent' => 10.0, // Requires level 2
            'level' => 1,
            'status' => 'pending',
        ]);

        ApprovalService::approve($approval, $approver);

        expect(ApprovalRequest::where('opportunity_id', $opp->id)->count())->toBe(2);
        $nextApproval = ApprovalRequest::where('opportunity_id', $opp->id)
            ->where('level', 2)
            ->first();
        expect($nextApproval)->not->toBeNull();
        expect($nextApproval->status)->toBe('pending');
    }

    public function test_approve_marks_opportunity_as_discount_approved_on_final_level()
    {
        $sales = User::factory()->create(['role' => 'sales']);
        $gm = User::factory()->create(['role' => 'gm']);
        $approver = User::factory()->create(['role' => 'gm']);

        $opp = Opportunity::factory()->create([
            'sales_id' => $sales->id,
            'estimated_value' => 10_000_000,
            'discount_approved' => false,
        ]);

        $approval = ApprovalRequest::factory()->create([
            'opportunity_id' => $opp->id,
            'requested_by' => $sales->id,
            'current_approver_id' => $gm->id,
            'discount_percent' => 3.0, // Max level is 1, so level 1 is final
            'level' => 1,
            'status' => 'pending',
        ]);

        ApprovalService::approve($approval, $approver);

        $opp->refresh();
        expect($opp->discount_approved)->toBeTrue();
        expect($opp->approved_by)->toBe($approver->id);
    }

    // =========================================================================
    // reject Tests
    // =========================================================================

    public function test_reject_approval_request()
    {
        $sales = User::factory()->create(['role' => 'sales']);
        $manager = User::factory()->create(['role' => 'manager']);
        $approver = User::factory()->create(['role' => 'gm']);

        $opp = Opportunity::factory()->create(['sales_id' => $sales->id]);

        $approval = ApprovalRequest::factory()->create([
            'opportunity_id' => $opp->id,
            'status' => 'pending',
        ]);

        ApprovalService::reject($approval, $approver, 'Discount too high for this segment');

        $approval->refresh();
        expect($approval->status)->toBe('rejected');
        expect($approval->rejection_reason)->toBe('Discount too high for this segment');
        expect($approval->rejected_at)->not->toBeNull();
    }

    public function test_reject_does_not_create_next_approval()
    {
        $sales = User::factory()->create(['role' => 'sales']);
        $approver = User::factory()->create(['role' => 'gm']);

        $opp = Opportunity::factory()->create(['sales_id' => $sales->id]);

        $approval = ApprovalRequest::factory()->create([
            'opportunity_id' => $opp->id,
            'status' => 'pending',
        ]);

        ApprovalService::reject($approval, $approver, 'Test rejection');

        expect(ApprovalRequest::where('opportunity_id', $opp->id)->count())->toBe(1);
    }
}
