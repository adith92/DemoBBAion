<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Opportunity;
use App\Models\SalesTarget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KpiTest extends TestCase
{
    use RefreshDatabase;

    protected function makeSalesUser(): User
    {
        return User::factory()->create(['role' => 'sales']);
    }

    protected function makeClient(User $sales): Client
    {
        return Client::factory()->create(['assigned_sales_id' => $sales->id]);
    }

    protected function ensureTarget(User $sales): SalesTarget
    {
        return SalesTarget::getOrCreate(
            $sales->id,
            (int) now()->format('Y'),
            (int) now()->format('n')
        );
    }

    /** @test */
    public function actual_won_calculated_dynamically_from_database(): void
    {
        $sales  = $this->makeSalesUser();
        $client = $this->makeClient($sales);
        $target = $this->ensureTarget($sales);

        $this->assertEquals(0, $target->actual_won);

        Opportunity::factory()->create([
            'sales_id'    => $sales->id,
            'client_id'   => $client->id,
            'stage'       => 'won',
            'actual_close_date' => now(),
            'final_value' => 10_000_000,
        ]);

        $this->assertEquals(1, $target->fresh()->actual_won);
    }

    /** @test */
    public function actual_revenue_calculated_dynamically_from_database(): void
    {
        $sales  = $this->makeSalesUser();
        $client = $this->makeClient($sales);
        $target = $this->ensureTarget($sales);

        $this->assertEquals(0.0, (float) $target->actual_revenue);

        Opportunity::factory()->create([
            'sales_id'    => $sales->id,
            'client_id'   => $client->id,
            'stage'       => 'won',
            'actual_close_date' => now(),
            'final_value' => 25_000_000,
        ]);

        Opportunity::factory()->create([
            'sales_id'    => $sales->id,
            'client_id'   => $client->id,
            'stage'       => 'won',
            'actual_close_date' => now(),
            'final_value' => 15_000_000,
        ]);

        $this->assertEquals(40_000_000, (float) $target->fresh()->actual_revenue);
    }

    /** @test */
    public function actual_opportunities_calculated_dynamically_from_database(): void
    {
        $sales  = $this->makeSalesUser();
        $client = $this->makeClient($sales);
        $target = $this->ensureTarget($sales);

        $this->assertEquals(0, $target->actual_opportunities);

        Opportunity::factory()->create([
            'sales_id'  => $sales->id,
            'client_id' => $client->id,
            'stage'     => 'prospecting',
        ]);

        $this->assertEquals(1, $target->fresh()->actual_opportunities);
    }

    /** @test */
    public function activities_actuals_calculated_dynamically_from_database(): void
    {
        $sales  = $this->makeSalesUser();
        $target = $this->ensureTarget($sales);
        $client = $this->makeClient($sales);

        $this->assertEquals(0, $target->actual_meetings);
        $this->assertEquals(0, $target->actual_calls);
        $this->assertEquals(0, $target->actual_visits);

        // Create a meeting log
        \App\Models\ActivityLog::create([
            'sales_id' => $sales->id,
            'client_id' => $client->id,
            'type' => 'meeting',
            'subject' => 'Test Meeting',
            'activity_date' => now(),
        ]);

        // Create a call log
        \App\Models\ActivityLog::create([
            'sales_id' => $sales->id,
            'client_id' => $client->id,
            'type' => 'call',
            'subject' => 'Test Call',
            'activity_date' => now(),
        ]);

        // Create a visit log
        \App\Models\ActivityLog::create([
            'sales_id' => $sales->id,
            'client_id' => $client->id,
            'type' => 'visit',
            'subject' => 'Test Visit',
            'activity_date' => now(),
        ]);

        $this->assertEquals(1, $target->fresh()->actual_meetings);
        $this->assertEquals(1, $target->fresh()->actual_calls);
        // Note: visits logic combines 'visit' and 'meeting' so 1 + 1 = 2
        $this->assertEquals(2, $target->fresh()->actual_visits);
    }
}
