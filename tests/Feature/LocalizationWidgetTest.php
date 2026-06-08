<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WidgetPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LocalizationWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_language_switch_updates_dashboard_locale(): void
    {
        $user = User::factory()->create([
            'role' => 'gm',
            'password' => Hash::make('password123'),
        ]);

        $this->get(route('language.switch', 'en'))
            ->assertRedirect();

        $this->actingAs($user)
            ->withSession(['locale' => 'en'])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Quick Shortcuts')
            ->assertSee('Corporate fleet performance increased', false);

        $this->actingAs($user)
            ->withSession(['locale' => 'id'])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Shortcut Cepat')
            ->assertSee('Performa fleet korporat naik', false);
    }

    public function test_widget_layout_saves_to_user_preferences(): void
    {
        $user = User::factory()->create(['role' => 'gm']);

        $widgets = [
            ['id' => 'kpi-row', 'label' => 'KPI Cards', 'visible' => false, 'order' => 1],
            ['id' => 'quick-shortcuts', 'label' => 'Quick Shortcuts', 'visible' => true, 'order' => 2],
        ];

        $this->actingAs($user)
            ->postJson(route('widgets.save'), ['widgets' => $widgets])
            ->assertOk()
            ->assertJson(['status' => 'ok']);

        $this->assertDatabaseHas(WidgetPreference::class, [
            'user_id' => $user->id,
        ]);

        $this->assertFalse(WidgetPreference::first()->widgets[0]['visible']);
    }
}
