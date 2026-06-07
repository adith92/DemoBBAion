<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Client;
use App\Models\Booking;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClickableElementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_elements_are_clickable_in_bookings_index()
    {
        $sales = User::factory()->create(['role' => 'sales']);
        $user = User::factory()->create(['role' => 'gm']);
        $client = Client::factory()->create(['company_name' => 'Acme Corp', 'pic_name' => 'John Doe', 'email' => 'john@acme.com']);
        $vehicle = Vehicle::factory()->create();
        $booking = Booking::factory()->create(['client_id' => $client->id, 'sales_id' => $sales->id, 'vehicle_id' => $vehicle->id, 'status' => 'pending']);

        $response = $this->actingAs($user)->get(route('bookings.index'));

        $response->assertStatus(200);
        // Assert sales name is in a link
        $response->assertSee(route('sales.performance', $sales->id));
        $response->assertSee($sales->name);
        // Assert client name is in a link
        $response->assertSee(route('clients.show', $client->id));
        $response->assertSee($client->company_name);
        // Assert breadcrumbs link
        $response->assertSee('href="' . route('dashboard') . '"', false);
    }

    public function test_pic_is_clickable_in_clients_show()
    {
        $user = User::factory()->create(['role' => 'manager']);
        $client = Client::factory()->create(['company_name' => 'Acme Corp', 'pic_name' => 'John Doe', 'email' => 'john@acme.com']);

        $response = $this->actingAs($user)->get(route('clients.show', $client->id));

        $response->assertStatus(200);
        // Assert PIC name links to mailto
        $response->assertSee('mailto:' . $client->email);
        $response->assertSee($client->pic_name);
    }
}
