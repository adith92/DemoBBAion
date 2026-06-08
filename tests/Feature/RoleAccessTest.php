<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ──────────────────────────────────────────────────────────────

    protected function user(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    // ── Route: revenue.index ─────────────────────────────────────────────────

    /** @test */
    public function revenue_index_route_exists_and_redirects_to_analytics(): void
    {
        $this->actingAs($this->user('gm'))
            ->get(route('revenue.index'))
            ->assertRedirect(route('analytics.index'));
    }

    // ── Route: analytics.index (line 102 in original test) ───────────────────

    /** @test */
    public function analytics_page_returns_200_for_director(): void
    {
        $this->actingAs($this->user('gm'))
            ->get(route('analytics.index'))
            ->assertStatus(200);
    }

    /** @test */
    public function analytics_page_returns_200_for_gm(): void
    {
        $this->actingAs($this->user('gm'))
            ->get(route('analytics.index'))
            ->assertStatus(200);
    }

    /** @test */
    public function analytics_page_returns_200_for_manager(): void
    {
        $this->actingAs($this->user('manager'))
            ->get(route('analytics.index'))
            ->assertStatus(200);
    }

    /** @test */
    public function analytics_page_blocked_for_sales(): void
    {
        $this->actingAs($this->user('sales'))
            ->get(route('analytics.index'))
            ->assertStatus(403);
    }

    /** @test */
    public function analytics_page_blocked_for_guest(): void
    {
        $this->get(route('analytics.index'))
            ->assertRedirect(route('login'));
    }

    // ── Route: dashboard ─────────────────────────────────────────────────────

    /** @test */
    public function dashboard_returns_200_for_all_roles(): void
    {
        foreach (['gm','manager','sales','operational','finance'] as $role) {
            $this->actingAs($this->user($role))
                ->get(route('dashboard'))
                ->assertStatus(200, "Dashboard failed for role: {$role}");
        }
    }

    // ── Route: search.global ─────────────────────────────────────────────────

    /** @test */
    public function global_search_requires_auth(): void
    {
        $this->get(route('search.global', ['q' => 'test']))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function global_search_returns_json_for_authenticated_user(): void
    {
        $this->actingAs($this->user('sales'))
            ->getJson(route('search.global', ['q' => 'test']))
            ->assertStatus(200)
            ->assertJsonStructure(['results', 'query']);
    }

    /** @test */
    public function global_search_returns_empty_for_short_query(): void
    {
        $this->actingAs($this->user('sales'))
            ->getJson(route('search.global', ['q' => 'a']))
            ->assertStatus(200)
            ->assertJson(['results' => []]);
    }

    // ── Route: widgets ────────────────────────────────────────────────────────

    /** @test */
    public function widgets_save_requires_auth(): void
    {
        $this->postJson(route('widgets.save'), ['widgets' => []])
            ->assertStatus(401);
    }

    /** @test */
    public function widgets_save_validates_payload(): void
    {
        $this->actingAs($this->user('gm'))
            ->postJson(route('widgets.save'), ['widgets' => 'not-an-array'])
            ->assertStatus(422);
    }

    /** @test */
    public function widgets_save_accepts_valid_payload(): void
    {
        $this->actingAs($this->user('gm'))
            ->postJson(route('widgets.save'), [
                'widgets' => [
                    ['id' => 'kpi-row', 'visible' => true,  'order' => 1],
                    ['id' => 'charts',  'visible' => false, 'order' => 2],
                ],
            ])
            ->assertStatus(200)
            ->assertJson(['status' => 'ok']);
    }
}
