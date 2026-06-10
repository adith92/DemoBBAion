<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Client;
use App\Models\Opportunity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientTransactionsTest extends TestCase
{
    use RefreshDatabase;

    protected function user(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    /** @test */
    public function clients_index_displays_transaction_columns_for_gm()
    {
        $gm = $this->user('gm');
        $client = Client::factory()->create(['status' => 'active']);

        // Create a won opportunity
        Opportunity::factory()->create([
            'client_id' => $client->id,
            'stage' => 'won',
            'final_value' => 1500000000, // 1.5 Miliar
        ]);

        // Create another opportunity (not won) which shouldn't count towards the sum/count
        Opportunity::factory()->create([
            'client_id' => $client->id,
            'stage' => 'negotiation',
            'estimated_value' => 500000000,
        ]);

        $response = $this->actingAs($gm)
            ->get(route('clients.index'));

        $response->assertStatus(200);
        $response->assertSee('Jumlah Transaksi');
        $response->assertSee('Nilai Transaksi');
        
        // Assert the computed values are displayed
        $response->assertSee('1 won'); // or "1" depending on how we format it
        $response->assertSee('Rp 1.5 Miliar');
    }

    /** @test */
    public function clients_index_filters_by_status()
    {
        $gm = $this->user('gm');
        $activeClient = Client::factory()->create(['company_name' => 'Active Client Corp', 'status' => 'active']);
        $inactiveClient = Client::factory()->create(['company_name' => 'Inactive Client Corp', 'status' => 'inactive']);

        // Test filtering active
        $response = $this->actingAs($gm)
            ->get(route('clients.index', ['filter_status' => 'active']));

        $response->assertStatus(200);
        $response->assertSee('Active Client Corp');
        $response->assertDontSee('Inactive Client Corp');

        // Test filtering inactive
        $response2 = $this->actingAs($gm)
            ->get(route('clients.index', ['filter_status' => 'inactive']));

        $response2->assertStatus(200);
        $response2->assertSee('Inactive Client Corp');
        $response2->assertDontSee('Active Client Corp');
    }

    /** @test */
    public function clients_index_sorts_by_transactions_and_value()
    {
        $gm = $this->user('gm');
        
        $clientA = Client::factory()->create(['company_name' => 'Client A', 'status' => 'active']);
        $clientB = Client::factory()->create(['company_name' => 'Client B', 'status' => 'active']);

        // Client A has 2 won deals totaling 2 Miliar
        Opportunity::factory()->create(['client_id' => $clientA->id, 'stage' => 'won', 'final_value' => 1000000000]);
        Opportunity::factory()->create(['client_id' => $clientA->id, 'stage' => 'won', 'final_value' => 1000000000]);

        // Client B has 1 won deal totaling 3 Miliar
        Opportunity::factory()->create(['client_id' => $clientB->id, 'stage' => 'won', 'final_value' => 3000000000]);

        // Sort by won_opportunities_count descending
        $responseCount = $this->actingAs($gm)
            ->get(route('clients.index', ['sort_by' => 'transactions_desc']));
        
        $responseCount->assertStatus(200);
        // Client A (2 transactions) should come before Client B (1 transaction)
        $contentCount = $responseCount->getContent();
        $posA = strpos($contentCount, 'Client A');
        $posB = strpos($contentCount, 'Client B');
        $this->assertTrue($posA < $posB, "Client A should appear before Client B when sorting by transactions count");

        // Sort by won_opportunities_sum descending
        $responseSum = $this->actingAs($gm)
            ->get(route('clients.index', ['sort_by' => 'value_desc']));

        $responseSum->assertStatus(200);
        // Client B (3 Miliar) should come before Client A (2 Miliar)
        $contentSum = $responseSum->getContent();
        $posA_sum = strpos($contentSum, 'Client A');
        $posB_sum = strpos($contentSum, 'Client B');
        $this->assertTrue($posB_sum < $posA_sum, "Client B should appear before Client A when sorting by transactions value");
    }

    /** @test */
    public function client_show_displays_opportunity_statistics_and_history()
    {
        $gm = $this->user('gm');
        $client = Client::factory()->create();

        // Create won opportunity
        Opportunity::factory()->create([
            'client_id' => $client->id,
            'title' => 'CRM Deal 1',
            'stage' => 'won',
            'final_value' => 500000000,
        ]);

        // Create a negotiation opportunity
        Opportunity::factory()->create([
            'client_id' => $client->id,
            'title' => 'ERP Deal 2',
            'stage' => 'negotiation',
            'estimated_value' => 300000000,
        ]);

        $response = $this->actingAs($gm)
            ->get(route('clients.show', $client->id));

        $response->assertStatus(200);
        $response->assertSee('Jumlah Transaksi (Won)', false);
        $response->assertSee('1 won');
        $response->assertSee('CRM Deal 1');
        $response->assertSee('ERP Deal 2');
    }
}

