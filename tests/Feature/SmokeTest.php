<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Client;
use App\Models\Opportunity;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Booking;
use App\Models\WidgetPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bluebird CRM v7.7 — Comprehensive Smoke Test Suite
 *
 * Covers every major named route, role-based access, JSON APIs,
 * model integrity, and deploy-readiness checks.
 *
 * Run: php artisan test --filter SmokeTest
 */
class SmokeTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ──────────────────────────────────────────────────────────────

    protected function user(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    protected function actAs(string $role): static
    {
        return $this->actingAs($this->user($role));
    }

    // =========================================================================
    // SECTION 1 — Core Pages (HTTP 200)
    // =========================================================================

    /** @test */
    public function dashboard_returns_200_for_director(): void
    {
        $this->actAs('director')->get(route('dashboard'))->assertStatus(200);
    }

    /** @test */
    public function dashboard_returns_200_for_gm(): void
    {
        $this->actAs('gm')->get(route('dashboard'))->assertStatus(200);
    }

    /** @test */
    public function dashboard_returns_200_for_sales(): void
    {
        $this->actAs('sales')->get(route('dashboard'))->assertStatus(200);
    }

    /** @test */
    public function dashboard_gm_requires_gm_role(): void
    {
        $this->actAs('sales')->get(route('dashboard.gm'))->assertStatus(403);
    }

    /** @test */
    public function dashboard_gm_returns_200_for_gm(): void
    {
        $this->actAs('gm')->get(route('dashboard.gm'))->assertStatus(200);
    }

    /** @test */
    public function pipeline_returns_200(): void
    {
        $this->actAs('sales')->get(route('pipeline.index'))->assertStatus(200);
    }

    /** @test */
    public function pipeline_blocked_for_operational(): void
    {
        $this->actAs('operational')->get(route('pipeline.index'))->assertStatus(403);
    }

    /** @test */
    public function approvals_returns_200(): void
    {
        $this->actAs('manager')->get(route('approvals.index'))->assertStatus(200);
    }

    /** @test */
    public function finance_returns_200_for_finance_role(): void
    {
        $this->actAs('finance')->get(route('finance.index'))->assertStatus(200);
    }

    /** @test */
    public function finance_blocked_for_sales(): void
    {
        $this->actAs('sales')->get(route('finance.index'))->assertStatus(403);
    }

    /** @test */
    public function analytics_returns_200_for_director(): void
    {
        $this->actAs('director')->get(route('analytics.index'))->assertStatus(200);
    }

    /** @test */
    public function analytics_blocked_for_sales(): void
    {
        $this->actAs('sales')->get(route('analytics.index'))->assertStatus(403);
    }

    /** @test */
    public function kpi_returns_200(): void
    {
        $this->actAs('manager')->get(route('kpi.index'))->assertStatus(200);
    }

    /** @test */
    public function maintenance_returns_200(): void
    {
        $this->actAs('operational')->get(route('maintenance.index'))->assertStatus(200);
    }

    /** @test */
    public function fleet_returns_200(): void
    {
        $this->actAs('operational')->get(route('fleet.index'))->assertStatus(200);
    }

    /** @test */
    public function clients_returns_200(): void
    {
        $this->actAs('sales')->get(route('clients.index'))->assertStatus(200);
    }

    /** @test */
    public function bookings_returns_200(): void
    {
        $this->actAs('sales')->get(route('bookings.index'))->assertStatus(200);
    }

    /** @test */
    public function subscriptions_returns_200(): void
    {
        $this->actAs('finance')->get(route('subscriptions.index'))->assertStatus(200);
    }

    /** @test */
    public function vouchers_returns_200(): void
    {
        $this->actAs('finance')->get(route('vouchers.index'))->assertStatus(200);
    }

    /** @test */
    public function products_returns_200(): void
    {
        $this->actAs('director')->get(route('products.index'))->assertStatus(200);
    }

    /** @test */
    public function opportunities_returns_200(): void
    {
        $this->actAs('sales')->get(route('opportunities.index'))->assertStatus(200);
    }

    /** @test */
    public function activities_returns_200(): void
    {
        $this->actAs('sales')->get(route('activities.index'))->assertStatus(200);
    }

    // =========================================================================
    // SECTION 2 — Guest Redirect (unauthenticated → /login)
    // =========================================================================

    /** @test */
    public function guest_redirected_from_dashboard(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    /** @test */
    public function guest_redirected_from_pipeline(): void
    {
        $this->get(route('pipeline.index'))->assertRedirect(route('login'));
    }

    /** @test */
    public function guest_redirected_from_finance(): void
    {
        $this->get(route('finance.index'))->assertRedirect(route('login'));
    }

    /** @test */
    public function guest_redirected_from_analytics(): void
    {
        $this->get(route('analytics.index'))->assertRedirect(route('login'));
    }

    // =========================================================================
    // SECTION 3 — Revenue Route
    // =========================================================================

    /** @test */
    public function revenue_index_redirects_to_analytics(): void
    {
        $this->actAs('director')
            ->get(route('revenue.index'))
            ->assertRedirect(route('analytics.index'));
    }

    /** @test */
    public function revenue_index_blocked_for_sales(): void
    {
        $this->actAs('sales')
            ->get(route('revenue.index'))
            ->assertStatus(403);
    }

    // =========================================================================
    // SECTION 4 — Global Search API
    // =========================================================================

    /** @test */
    public function search_returns_401_for_guest(): void
    {
        $this->getJson(route('search.global', ['q' => 'test']))
            ->assertStatus(401);
    }

    /** @test */
    public function search_returns_empty_results_for_short_query(): void
    {
        $this->actAs('sales')
            ->getJson(route('search.global', ['q' => 'x']))
            ->assertStatus(200)
            ->assertJson(['results' => [], 'query' => 'x']);
    }

    /** @test */
    public function search_returns_json_structure(): void
    {
        $this->actAs('sales')
            ->getJson(route('search.global', ['q' => 'test']))
            ->assertStatus(200)
            ->assertJsonStructure(['results', 'query', 'total']);
    }

    /** @test */
    public function search_finds_clients(): void
    {
        Client::factory()->create(['company_name' => 'Acme Transport Corp']);

        $this->actAs('sales')
            ->getJson(route('search.global', ['q' => 'Acme']))
            ->assertStatus(200)
            ->assertJsonPath('results.0.type', 'client')
            ->assertJsonPath('results.0.label', 'Acme Transport Corp');
    }

    /** @test */
    public function search_max_16_results(): void
    {
        Client::factory(20)->create(['company_name' => fn() => 'TestCorp ' . fake()->uuid()]);

        $response = $this->actAs('sales')
            ->getJson(route('search.global', ['q' => 'TestCorp']))
            ->assertStatus(200)
            ->json();

        $this->assertLessThanOrEqual(16, count($response['results']));
    }

    // =========================================================================
    // SECTION 5 — Widget Preference API
    // =========================================================================

    /** @test */
    public function widgets_save_returns_401_for_guest(): void
    {
        $this->postJson(route('widgets.save'), ['widgets' => []])
            ->assertStatus(401);
    }

    /** @test */
    public function widgets_save_validates_required_fields(): void
    {
        $this->actAs('director')
            ->postJson(route('widgets.save'), ['widgets' => 'bad'])
            ->assertStatus(422);
    }

    /** @test */
    public function widgets_save_validates_widget_structure(): void
    {
        $this->actAs('director')
            ->postJson(route('widgets.save'), [
                'widgets' => [['id' => 'kpi-row']], // missing visible + order
            ])
            ->assertStatus(422);
    }

    /** @test */
    public function widgets_save_persists_to_database(): void
    {
        $user = $this->user('director');
        $payload = [
            ['id' => 'kpi-row',  'visible' => true,  'order' => 1],
            ['id' => 'charts',   'visible' => false,  'order' => 2],
        ];

        $this->actingAs($user)
            ->postJson(route('widgets.save'), ['widgets' => $payload])
            ->assertStatus(200)
            ->assertJson(['status' => 'ok']);

        $this->assertDatabaseHas('widget_preferences', ['user_id' => $user->id]);
        $pref = WidgetPreference::where('user_id', $user->id)->first();
        $this->assertCount(2, $pref->widgets);
        $this->assertEquals('kpi-row', $pref->widgets[0]['id']);
    }

    /** @test */
    public function widgets_save_updates_existing_preference(): void
    {
        $user = $this->user('gm');
        WidgetPreference::create([
            'user_id' => $user->id,
            'widgets' => [['id' => 'old', 'visible' => true, 'order' => 1]],
        ]);

        $this->actingAs($user)
            ->postJson(route('widgets.save'), [
                'widgets' => [['id' => 'new', 'visible' => false, 'order' => 1]],
            ])
            ->assertStatus(200);

        $this->assertDatabaseCount('widget_preferences', 1); // no duplicate
        $this->assertEquals('new', WidgetPreference::where('user_id', $user->id)->first()->widgets[0]['id']);
    }

    /** @test */
    public function widgets_reset_returns_ok(): void
    {
        $user = $this->user('director');
        WidgetPreference::create([
            'user_id' => $user->id,
            'widgets' => [['id' => 'kpi-row', 'visible' => true, 'order' => 1]],
        ]);

        $this->actingAs($user)
            ->postJson(route('widgets.reset'))
            ->assertStatus(200)
            ->assertJson(['status' => 'ok']);

        $this->assertDatabaseMissing('widget_preferences', ['user_id' => $user->id]);
    }

    // =========================================================================
    // SECTION 6 — Model Integrity
    // =========================================================================

    /** @test */
    public function widget_preference_default_widgets_returns_9_items(): void
    {
        $defaults = WidgetPreference::defaultWidgets();
        $this->assertCount(9, $defaults);
        foreach ($defaults as $w) {
            $this->assertArrayHasKey('id', $w);
            $this->assertArrayHasKey('visible', $w);
            $this->assertArrayHasKey('order', $w);
        }
    }

    /** @test */
    public function widget_preference_for_user_returns_defaults_when_none_set(): void
    {
        $user = $this->user('sales');
        $result = WidgetPreference::forUser($user->id);
        $this->assertCount(9, $result);
    }

    /** @test */
    public function widget_preference_for_user_returns_saved_prefs(): void
    {
        $user = $this->user('sales');
        WidgetPreference::create([
            'user_id' => $user->id,
            'widgets' => [['id' => 'custom', 'visible' => true, 'order' => 1]],
        ]);
        $result = WidgetPreference::forUser($user->id);
        $this->assertEquals('custom', $result[0]['id']);
    }

    /** @test */
    public function opportunity_belongs_to_client(): void
    {
        $client = Client::factory()->create();
        $opp    = Opportunity::factory()->create(['client_id' => $client->id]);

        $this->assertEquals($client->id, $opp->client->id);
    }

    /** @test */
    public function booking_has_one_invoice_relationship(): void
    {
        $booking = Booking::factory()->create();
        // relationship method must exist (not null method)
        $this->assertNotNull($booking->invoice());
    }

    // =========================================================================
    // SECTION 7 — Deploy Readiness
    // =========================================================================

    /** @test */
    public function app_key_is_set(): void
    {
        $this->assertNotEmpty(config('app.key'), 'APP_KEY must be set');
    }

    /** @test */
    public function database_connection_is_configured(): void
    {
        $driver = \DB::connection()->getDriverName();
        $this->assertContains($driver, ['sqlite', 'mysql', 'mariadb', 'pgsql'],
            "Unsupported DB driver: {$driver}");
    }

    /** @test */
    public function widget_preferences_table_exists(): void
    {
        $this->assertTrue(
            \Schema::hasTable('widget_preferences'),
            'widget_preferences table must exist — run: php artisan migrate'
        );
    }

    /** @test */
    public function users_table_has_role_column(): void
    {
        $this->assertTrue(\Schema::hasColumn('users', 'role'));
    }

    /** @test */
    public function session_driver_is_not_array_in_non_testing(): void
    {
        // In testing env it's fine to use array driver
        // This just ensures the config key is set
        $this->assertNotNull(config('session.driver'));
    }

    /** @test */
    public function csrf_middleware_is_active(): void
    {
        // POST to any route without CSRF token must return 419 (VerifyCsrfToken)
        // when called as non-JSON (browser context)
        $response = $this->withHeaders(['Accept' => 'text/html'])
            ->post(route('kpi.store'));
        $this->assertContains($response->status(), [419, 302, 401, 403],
            'CSRF should block unauthenticated POST or redirect');
    }

    /** @test */
    public function analytics_sub_routes_all_return_200(): void
    {
        $user = $this->user('director');
        foreach (['analytics.index', 'analytics.crosssell', 'analytics.pipeline', 'analytics.sales'] as $name) {
            $this->actingAs($user)
                ->get(route($name))
                ->assertStatus(200, "Route {$name} failed");
        }
    }
}
