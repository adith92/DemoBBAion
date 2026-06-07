<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Opportunity;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PipelineTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function makeSalesUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge(['role' => 'sales'], $overrides));
    }

    protected function makeClient(User $sales): Client
    {
        return Client::factory()->create(['assigned_sales_id' => $sales->id]);
    }

    protected function makeOneOffProduct(): Product
    {
        $category = ProductCategory::factory()->create(['type' => 'short_term']);
        return Product::factory()->create(['product_category_id' => $category->id]);
    }

    // -------------------------------------------------------------------------
    // Tests
    // -------------------------------------------------------------------------

    /**
     * Sales user can POST /opportunities and the record is persisted with the
     * correct sales_id matching the authenticated user.
     */
    public function test_sales_can_create_opportunity(): void
    {
        $sales  = $this->makeSalesUser();
        $client = $this->makeClient($sales);

        $response = $this->actingAs($sales)
            ->withSession(['_token' => 'test-token'])
            ->post('/opportunities', [
                'title'               => 'New Fleet Deal',
                'client_id'           => $client->id,
                'stage'               => 'prospecting',
                'estimated_value'     => 5000000,
                'expected_close_date' => now()->addMonths(2)->toDateString(),
                '_token'              => 'test-token',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('opportunities', [
            'title'    => 'New Fleet Deal',
            'sales_id' => $sales->id,
            'stage'    => 'prospecting',
        ]);
    }

    /**
     * An opportunity can advance from prospecting to proposal when the
     * advance-stage endpoint is called with a valid transition.
     */
    public function test_opportunity_stage_advance(): void
    {
        $sales  = $this->makeSalesUser();
        $client = $this->makeClient($sales);

        $opportunity = Opportunity::factory()->create([
            'sales_id'  => $sales->id,
            'client_id' => $client->id,
            'stage'     => 'prospecting',
        ]);

        $response = $this->actingAs($sales)
            ->withSession(['_token' => 'test-token'])
            ->post("/opportunities/{$opportunity->id}/advance-stage", [
                'stage'  => 'proposal',
                '_token' => 'test-token',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('opportunities', [
            'id'    => $opportunity->id,
            'stage' => 'proposal',
        ]);
    }

    /**
     * Skipping from prospecting directly to won is now allowed
     * due to free movement kanban logic.
     */
    public function test_can_skip_stages(): void
    {
        $sales  = $this->makeSalesUser();
        $client = $this->makeClient($sales);

        $opportunity = Opportunity::factory()->create([
            'sales_id'  => $sales->id,
            'client_id' => $client->id,
            'stage'     => 'prospecting',
        ]);

        $response = $this->actingAs($sales)
            ->withSession(['_token' => 'test-token'])
            ->post("/opportunities/{$opportunity->id}/advance-stage", [
                'stage'  => 'won',
                '_token' => 'test-token',
            ]);

        // Expect a successful redirect without errors
        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // Stage must be updated
        $this->assertDatabaseHas('opportunities', [
            'id'    => $opportunity->id,
            'stage' => 'won',
        ]);
    }

    /**
     * Advancing a one-off opportunity to won should cause an Invoice record
     * to be created in the database.
     */
    public function test_won_opportunity_creates_invoice(): void
    {
        $sales   = $this->makeSalesUser();
        $client  = $this->makeClient($sales);
        $product = $this->makeOneOffProduct();

        // Walk the pipeline to negotiation first so won is a valid transition
        $opportunity = Opportunity::factory()->create([
            'sales_id'        => $sales->id,
            'client_id'       => $client->id,
            'stage'           => 'negotiation',
            'product_id'      => $product->id,
            'estimated_value' => 10000000,
            'final_value'     => 10000000,
        ]);

        $invoicesBefore = Invoice::count();

        $response = $this->actingAs($sales)
            ->withSession(['_token' => 'test-token'])
            ->post("/opportunities/{$opportunity->id}/advance-stage", [
                'stage'  => 'won',
                '_token' => 'test-token',
            ]);

        $response->assertRedirect();

        // At least one new invoice should have been created
        $this->assertGreaterThan($invoicesBefore, Invoice::count());
    }

    /**
     * Each sales user should only see their own opportunities on the index page
     * (the query is scoped by sales_id for the 'sales' role).
     */
    public function test_opportunity_scoped_to_sales(): void
    {
        $sales1 = $this->makeSalesUser(['name' => 'Sales One']);
        $sales2 = $this->makeSalesUser(['name' => 'Sales Two']);

        $client1 = $this->makeClient($sales1);
        $client2 = $this->makeClient($sales2);

        $opp1 = Opportunity::factory()->create([
            'sales_id'  => $sales1->id,
            'client_id' => $client1->id,
            'title'     => 'Opp For Sales One',
        ]);

        $opp2 = Opportunity::factory()->create([
            'sales_id'  => $sales2->id,
            'client_id' => $client2->id,
            'title'     => 'Opp For Sales Two',
        ]);

        // sales1 should only see their own opportunity
        $this->assertEquals(1, Opportunity::where('sales_id', $sales1->id)->count());
        $this->assertEquals(1, Opportunity::where('sales_id', $sales2->id)->count());

        // Via HTTP: sales1 should not see sales2's opportunity in the index
        $response = $this->actingAs($sales1)->get('/opportunities');
        $response->assertOk();
        $response->assertDontSee('Opp For Sales Two');
        $response->assertSee('Opp For Sales One');
    }
}
