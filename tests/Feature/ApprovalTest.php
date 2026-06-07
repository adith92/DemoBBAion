<?php

namespace Tests\Feature;

use App\Models\ApprovalRequest;
use App\Models\Client;
use App\Models\Opportunity;
use App\Models\User;
use App\Services\ApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function makeSalesUser(): User
    {
        return User::factory()->create(['role' => 'sales']);
    }

    protected function makeManagerUser(): User
    {
        return User::factory()->create(['role' => 'manager']);
    }

    protected function makeGMUser(): User
    {
        return User::factory()->create(['role' => 'gm']);
    }

    protected function makeDirectorUser(): User
    {
        return User::factory()->create(['role' => 'director']);
    }

    protected function makeOpportunity(User $sales, array $overrides = []): Opportunity
    {
        $client = Client::factory()->create(['assigned_sales_id' => $sales->id]);

        return Opportunity::factory()->create(array_merge([
            'sales_id'        => $sales->id,
            'client_id'       => $client->id,
            'stage'           => 'negotiation',
            'estimated_value' => 10_000_000,
        ], $overrides));
    }

    // -------------------------------------------------------------------------
    // Tests
    // -------------------------------------------------------------------------

    /**
     * A 3% discount (<=5%) must generate an ApprovalRequest at level 1 (manager).
     */
    public function test_discount_under_5_requires_manager_approval(): void
    {
        $sales   = $this->makeSalesUser();
        $manager = $this->makeManagerUser();
        $opp     = $this->makeOpportunity($sales, ['estimated_value' => 10_000_000]);

        $response = $this->actingAs($sales)
            ->withSession(['_token' => 'test-token'])
            ->post("/opportunities/{$opp->id}/discount", [
                'discount_percent' => 3,
                '_token'           => 'test-token',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('approval_requests', [
            'opportunity_id' => $opp->id,
            'level'          => 1,
            'status'         => 'pending',
            'type'           => 'discount',
        ]);
    }

    /**
     * A 20% discount (>15%) should begin as level 1, and after approving it,
     * the chain should escalate to a new ApprovalRequest at level 2.
     */
    public function test_discount_over_15_requires_all_levels(): void
    {
        $sales    = $this->makeSalesUser();
        $manager  = $this->makeManagerUser();
        $gm       = $this->makeGMUser();
        $director = $this->makeDirectorUser();

        $opp = $this->makeOpportunity($sales, ['estimated_value' => 10_000_000]);

        // Create the approval chain (starts at level 1 for a 10jt deal)
        $req = ApprovalService::createApprovalChain($opp, 20.0);

        $this->assertDatabaseHas('approval_requests', [
            'opportunity_id' => $opp->id,
            'level'          => 1,
            'status'         => 'pending',
        ]);

        // Approve level 1 — should escalate to level 2
        ApprovalService::approve($req, $manager);

        $this->assertDatabaseHas('approval_requests', [
            'opportunity_id' => $opp->id,
            'level'          => 2,
            'status'         => 'pending',
        ]);
    }

    /**
     * A deal value > 50jt should start the approval at level 2 (gm), skipping
     * level 1 (manager) regardless of discount percentage.
     */
    public function test_large_deal_skips_manager(): void
    {
        $sales = $this->makeSalesUser();
        $gm    = $this->makeGMUser();

        $opp = $this->makeOpportunity($sales, ['estimated_value' => 60_000_000]);

        $req = ApprovalService::createApprovalChain($opp, 5.0);

        $this->assertDatabaseHas('approval_requests', [
            'opportunity_id' => $opp->id,
            'level'          => 2,
            'status'         => 'pending',
        ]);
    }

    /**
     * Approving through all required levels should set discount_approved=true
     * on the opportunity.
     */
    public function test_approved_discount_sets_flag(): void
    {
        $sales   = $this->makeSalesUser();
        $manager = $this->makeManagerUser();

        // 3% discount requires only level 1 (manager)
        $opp = $this->makeOpportunity($sales, ['estimated_value' => 10_000_000]);

        $req = ApprovalService::createApprovalChain($opp, 3.0);

        // Approve the single required level
        ApprovalService::approve($req, $manager);

        $this->assertDatabaseHas('opportunities', [
            'id'               => $opp->id,
            'discount_approved'=> 1, // boolean true stored as 1
        ]);
    }

    /**
     * Rejecting an approval request should set its status to 'rejected' and
     * persist the rejection_reason.
     */
    public function test_rejected_approval_notification(): void
    {
        $sales   = $this->makeSalesUser();
        $manager = $this->makeManagerUser();

        $opp = $this->makeOpportunity($sales, ['estimated_value' => 10_000_000]);

        $req = ApprovalService::createApprovalChain($opp, 5.0);

        ApprovalService::reject($req, $manager, 'Diskon tidak sesuai kebijakan.');

        $this->assertDatabaseHas('approval_requests', [
            'id'               => $req->id,
            'status'           => 'rejected',
            'rejection_reason' => 'Diskon tidak sesuai kebijakan.',
        ]);

        $this->assertNotNull($req->fresh()->rejected_at);
    }
}
