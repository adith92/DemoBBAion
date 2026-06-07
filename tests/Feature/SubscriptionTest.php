<?php

namespace Tests\Feature;

use App\Http\Controllers\SubscriptionController;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function makeFinanceUser(): User
    {
        return User::factory()->create(['role' => 'finance']);
    }

    protected function makeClient(): Client
    {
        return Client::factory()->create();
    }

    /**
     * Create a Subscription record directly (bypassing HTTP) with given overrides.
     */
    protected function createSubscription(array $overrides = []): Subscription
    {
        $client = $overrides['client_id'] ?? $this->makeClient()->id;

        $defaults = [
            'client_id'         => $client,
            'start_date'        => today()->toDateString(),
            'end_date'          => today()->addYear()->toDateString(),
            'monthly_rate'      => 5_000_000,
            'billing_cycle'     => 'monthly',
            'status'            => 'active',
            'auto_renew'        => true,
            'next_billing_date' => today()->toDateString(),
        ];

        return Subscription::create(array_merge($defaults, $overrides));
    }

    // -------------------------------------------------------------------------
    // Tests
    // -------------------------------------------------------------------------

    /**
     * Authenticated finance user can POST /subscriptions and the record is
     * persisted in the database.
     */
    public function test_subscription_can_be_created(): void
    {
        $finance = $this->makeFinanceUser();
        $client  = $this->makeClient();

        $response = $this->actingAs($finance)
            ->withSession(['_token' => 'test-token'])
            ->post('/subscriptions', [
                '_token'        => 'test-token',
                'client_id'     => $client->id,
                'start_date'    => today()->toDateString(),
                'end_date'      => today()->addYear()->toDateString(),
                'monthly_rate'  => 5_000_000,
                'billing_cycle' => 'monthly',
                'auto_renew'    => true,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('subscriptions', [
            'client_id'    => $client->id,
            'monthly_rate' => 5_000_000,
            'status'       => 'active',
        ]);
    }

    /**
     * Running processMonthlyBilling for a subscription whose next_billing_date
     * is today (or in the past) should create an Invoice record.
     */
    public function test_billing_creates_invoice_on_due_date(): void
    {
        $sub = $this->createSubscription([
            'next_billing_date' => today()->toDateString(),
        ]);

        $invoicesBefore = Invoice::count();

        SubscriptionController::processMonthlyBilling();

        $this->assertGreaterThan($invoicesBefore, Invoice::count());

        $this->assertDatabaseHas('invoices', [
            'client_id' => $sub->client_id,
            'amount'    => $sub->monthly_rate,
            'status'    => 'sent',
        ]);
    }

    /**
     * Running processMonthlyBilling twice for the same subscription and period
     * must not create duplicate invoices (idempotency guard).
     */
    public function test_billing_is_idempotent(): void
    {
        $sub = $this->createSubscription([
            'next_billing_date' => today()->toDateString(),
        ]);

        // First run — creates invoice and advances next_billing_date
        SubscriptionController::processMonthlyBilling();
        $countAfterFirst = Invoice::count();

        // Manually reset next_billing_date to simulate a second call for the
        // same period (as if the billing date was not advanced yet)
        $billingPeriod = today()->format('Ym');
        $periodLabel   = 'SUB-BILLING/' . $sub->sub_number . '/' . $billingPeriod;

        // Ensure the idempotency note is present (it was set by first run)
        $existingInvoice = Invoice::where('notes', 'like', '%' . $periodLabel . '%')->first();
        $this->assertNotNull($existingInvoice, 'First run should have created an invoice with the period label.');

        // Re-set next_billing_date back to trigger condition — without changing sub_number or period
        $sub->update(['next_billing_date' => today()->toDateString()]);

        // Second run — should skip due to idempotency guard
        SubscriptionController::processMonthlyBilling();

        // Invoice count must remain the same
        $this->assertEquals($countAfterFirst, Invoice::count());
    }

    /**
     * A terminated subscription must not produce any Invoice when billing runs.
     */
    public function test_terminated_subscription_not_billed(): void
    {
        $sub = $this->createSubscription([
            'status'            => 'terminated',
            'next_billing_date' => today()->toDateString(),
        ]);

        $invoicesBefore = Invoice::count();

        SubscriptionController::processMonthlyBilling();

        $this->assertEquals($invoicesBefore, Invoice::count());
    }
}
