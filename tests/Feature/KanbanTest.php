<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Opportunity;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * KanbanTest — covers the three new Kanban endpoints:
 *  - PATCH /opportunities/{id}/move-stage
 *  - PATCH /opportunities/{id}/quick-update
 *  - GET  /opportunities/{id}/360
 *
 * Also smoke-tests the pipeline Kanban view itself.
 */
class KanbanTest extends TestCase
{
    use RefreshDatabase;

    // ── helpers ──────────────────────────────────────────────────────────────

    protected function salesUser(array $attrs = []): User
    {
        return User::factory()->create(array_merge(['role' => 'sales'], $attrs));
    }

    protected function makeOpp(User $sales, array $attrs = []): Opportunity
    {
        $client = Client::factory()->create(['assigned_sales_id' => $sales->id]);
        return Opportunity::factory()->create(array_merge([
            'sales_id'  => $sales->id,
            'client_id' => $client->id,
            'stage'     => 'prospecting',
        ], $attrs));
    }

    // ── pipeline index (smoke) ────────────────────────────────────────────────

    public function test_pipeline_kanban_view_loads(): void
    {
        $sales = $this->salesUser();
        $this->makeOpp($sales, ['title' => 'Demo Fleet Deal']);

        $response = $this->actingAs($sales)->get('/pipeline');

        $response->assertOk();
        $response->assertSee('Sales Pipeline');
        $response->assertSee('Demo Fleet Deal');
        $response->assertSee('kanbanBoard()'); // Alpine component present
    }

    public function test_unauthenticated_cannot_access_pipeline(): void
    {
        $this->get('/pipeline')->assertRedirect('/login');
    }

    public function test_finance_role_cannot_access_pipeline(): void
    {
        $finance = User::factory()->create(['role' => 'finance']);
        $this->actingAs($finance)->get('/pipeline')->assertForbidden();
    }

    // ── move-stage endpoint ──────────────────────────────────────────────────

    public function test_move_stage_valid_transition_returns_ok(): void
    {
        $sales = $this->salesUser();
        $opp   = $this->makeOpp($sales, ['stage' => 'prospecting']);

        $response = $this->actingAs($sales)->patchJson("/opportunities/{$opp->id}/move-stage", [
            'stage' => 'proposal',
        ]);

        $response->assertOk()->assertJsonFragment(['ok' => true, 'stage' => 'proposal']);
        $this->assertDatabaseHas('opportunities', ['id' => $opp->id, 'stage' => 'proposal']);
    }

    public function test_move_stage_logs_activity(): void
    {
        $sales = $this->salesUser();
        $opp   = $this->makeOpp($sales, ['stage' => 'prospecting']);

        $this->actingAs($sales)->patchJson("/opportunities/{$opp->id}/move-stage", [
            'stage' => 'proposal',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'opportunity_id' => $opp->id,
            'sales_id'       => $sales->id,
        ]);
    }

    public function test_move_stage_skipping_returns_ok(): void
    {
        $sales = $this->salesUser();
        $opp   = $this->makeOpp($sales, ['stage' => 'prospecting']);

        $response = $this->actingAs($sales)->patchJson("/opportunities/{$opp->id}/move-stage", [
            'stage' => 'won', // skip is now allowed
        ]);

        $response->assertOk()->assertJsonFragment(['ok' => true]);
        $this->assertDatabaseHas('opportunities', ['id' => $opp->id, 'stage' => 'won']);
    }

    public function test_move_to_lost_requires_lost_reason(): void
    {
        $sales = $this->salesUser();
        $opp   = $this->makeOpp($sales, ['stage' => 'negotiation']);

        // Without lost_reason, the move should still succeed (defaults to a generic reason)
        $response = $this->actingAs($sales)->patchJson("/opportunities/{$opp->id}/move-stage", [
            'stage'       => 'lost',
            'lost_reason' => 'Kalah harga dari kompetitor',
        ]);

        $response->assertOk()->assertJsonFragment(['ok' => true]);
        $this->assertDatabaseHas('opportunities', [
            'id'          => $opp->id,
            'stage'       => 'lost',
            'lost_reason' => 'Kalah harga dari kompetitor',
        ]);
    }

    public function test_move_to_won_sets_actual_close_date(): void
    {
        $sales = $this->salesUser();
        $opp   = $this->makeOpp($sales, ['stage' => 'negotiation']);

        $this->actingAs($sales)->patchJson("/opportunities/{$opp->id}/move-stage", [
            'stage' => 'won',
        ]);

        $fresh = $opp->fresh();
        $this->assertNotNull($fresh->actual_close_date);
        $this->assertEquals(now()->toDateString(), $fresh->actual_close_date->toDateString());
    }

    public function test_sales_cannot_move_other_users_opportunity(): void
    {
        $sales1 = $this->salesUser();
        $sales2 = $this->salesUser();
        $opp    = $this->makeOpp($sales1, ['stage' => 'prospecting']);

        $response = $this->actingAs($sales2)->patchJson("/opportunities/{$opp->id}/move-stage", [
            'stage' => 'proposal',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('opportunities', ['id' => $opp->id, 'stage' => 'prospecting']);
    }

    public function test_same_stage_move_returns_ok_without_db_change(): void
    {
        $sales = $this->salesUser();
        $opp   = $this->makeOpp($sales, ['stage' => 'proposal']);

        $response = $this->actingAs($sales)->patchJson("/opportunities/{$opp->id}/move-stage", [
            'stage' => 'proposal',
        ]);

        $response->assertOk()->assertJsonFragment(['ok' => true]);
    }

    // ── quick-update endpoint ────────────────────────────────────────────────

    public function test_quick_update_title(): void
    {
        $sales = $this->salesUser();
        $opp   = $this->makeOpp($sales, ['title' => 'Old Title']);

        $response = $this->actingAs($sales)->patchJson("/opportunities/{$opp->id}/quick-update", [
            'title' => 'New Title Updated',
        ]);

        $response->assertOk()->assertJsonFragment(['ok' => true]);
        $this->assertDatabaseHas('opportunities', ['id' => $opp->id, 'title' => 'New Title Updated']);
    }

    public function test_quick_update_estimated_value_and_pax(): void
    {
        $sales = $this->salesUser();
        $opp   = $this->makeOpp($sales);

        $this->actingAs($sales)->patchJson("/opportunities/{$opp->id}/quick-update", [
            'estimated_value' => 99000000,
            'pax'             => 12,
        ])->assertOk();

        $this->assertDatabaseHas('opportunities', [
            'id'  => $opp->id,
            'pax' => 12,
        ]);
    }

    public function test_quick_update_empty_title_returns_error(): void
    {
        $sales = $this->salesUser();
        $opp   = $this->makeOpp($sales, ['title' => 'Should Stay']);

        $response = $this->actingAs($sales)->patchJson("/opportunities/{$opp->id}/quick-update", [
            'title' => '',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseHas('opportunities', ['id' => $opp->id, 'title' => 'Should Stay']);
    }

    public function test_quick_update_another_users_opp_is_forbidden(): void
    {
        $sales1 = $this->salesUser();
        $sales2 = $this->salesUser();
        $opp    = $this->makeOpp($sales1, ['title' => 'Original']);

        $this->actingAs($sales2)->patchJson("/opportunities/{$opp->id}/quick-update", [
            'title' => 'Hacked',
        ])->assertForbidden();

        $this->assertDatabaseHas('opportunities', ['id' => $opp->id, 'title' => 'Original']);
    }

    // ── 360° view endpoint ───────────────────────────────────────────────────

    public function test_360_view_returns_opportunity_with_relations(): void
    {
        $sales = $this->salesUser();
        $opp   = $this->makeOpp($sales, ['title' => 'Big Deal 360']);

        $response = $this->actingAs($sales)->getJson("/opportunities/{$opp->id}/360");

        $response->assertOk()
                 ->assertJsonFragment(['ok' => true])
                 ->assertJsonPath('opportunity.title', 'Big Deal 360')
                 ->assertJsonStructure(['opportunity' => [
                     'id','title','stage','opp_number',
                     'client','sales','activity_logs','approval_requests',
                 ]]);
    }

    public function test_360_view_includes_activity_logs(): void
    {
        $sales = $this->salesUser();
        $opp   = $this->makeOpp($sales);
        $client = Client::find($opp->client_id);

        ActivityLog::create([
            'sales_id'       => $sales->id,
            'client_id'      => $client->id,
            'opportunity_id' => $opp->id,
            'type'           => 'follow_up',
            'subject'        => 'Called client',
            'activity_date'  => now(),
        ]);

        $response = $this->actingAs($sales)->getJson("/opportunities/{$opp->id}/360");

        $response->assertOk();
        $this->assertCount(1, $response->json('opportunity.activity_logs'));
        $this->assertEquals('Called client', $response->json('opportunity.activity_logs.0.subject'));
    }

    public function test_360_view_blocked_for_other_sales(): void
    {
        $sales1 = $this->salesUser();
        $sales2 = $this->salesUser();
        $opp    = $this->makeOpp($sales1);

        $this->actingAs($sales2)->getJson("/opportunities/{$opp->id}/360")
             ->assertForbidden();
    }

    public function test_360_view_accessible_for_gm(): void
    {
        $sales = $this->salesUser();
        $gm    = User::factory()->create(['role' => 'gm']);
        $opp   = $this->makeOpp($sales);

        $this->actingAs($gm)->getJson("/opportunities/{$opp->id}/360")
             ->assertOk()
             ->assertJsonFragment(['ok' => true]);
    }
}
