<?php

namespace App\Services;

use App\Models\Opportunity;
use App\Models\Subscription;
use App\Models\Invoice;

class PipelineService
{
    /**
     * All valid kanban stages (Trello-style free movement).
     * Any stage can move to any other — won/lost are soft exits only.
     */
    protected array $allStages = [
        'call_meeting', 'prospecting', 'proposal', 'negotiation', 'won', 'lost',
    ];

    /**
     * @deprecated Kept for backward compat; use $allStages for kanban.
     */
    protected array $transitions = [
        'call_meeting'  => ['prospecting', 'proposal', 'negotiation', 'won', 'lost'],
        'prospecting'   => ['call_meeting', 'proposal', 'negotiation', 'won', 'lost'],
        'proposal'      => ['call_meeting', 'prospecting', 'negotiation', 'won', 'lost'],
        'negotiation'   => ['call_meeting', 'prospecting', 'proposal', 'won', 'lost'],
        'won'           => ['call_meeting', 'prospecting', 'proposal', 'negotiation', 'lost'],
        'lost'          => ['call_meeting', 'prospecting', 'proposal', 'negotiation', 'won'],
        'qualification' => ['call_meeting', 'prospecting', 'proposal', 'negotiation', 'won', 'lost'],
        'closed'        => ['call_meeting', 'prospecting', 'proposal', 'negotiation', 'won', 'lost'],
    ];

    /**
     * Return the valid next stages from the given current stage.
     *
     * @param  string  $currentStage
     * @return string[]
     */
    public function getNextStages(string $currentStage): array
    {
        return $this->transitions[strtolower($currentStage)] ?? [];
    }

    /**
     * Determine whether a transition from one stage to another is allowed.
     *
     * @param  string  $from
     * @param  string  $to
     * @return bool
     */
    public function canTransition(string $from, string $to): bool
    {
        $nextStages = $this->getNextStages($from);
        return in_array(strtolower($to), $nextStages, true);
    }

    /**
     * Trigger post-won actions for an opportunity.
     *
     * Creates a Subscription (for recurring deals) or an Invoice (for
     * one-time deals) linked to the opportunity.
     *
     * @param  Opportunity  $opportunity
     * @return Subscription|Invoice
     */
    public function triggerWonActions(Opportunity $opportunity): Subscription|Invoice
    {
        if ($this->isRecurring($opportunity)) {
            return $this->createSubscription($opportunity);
        }

        return $this->createInvoice($opportunity);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Decide whether the opportunity results in a recurring subscription.
     * Extend this logic to suit your product catalogue.
     */
    protected function isRecurring(Opportunity $opportunity): bool
    {
        if (isset($opportunity->type) && $opportunity->type === 'recurring') {
            return true;
        }

        if (is_array($opportunity->products)) {
            foreach ($opportunity->products as $p) {
                if (isset($p['category']) && str_contains($p['category'], 'Long Term')) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Create a Subscription record linked to the won opportunity.
     */
    protected function createSubscription(Opportunity $opportunity): Subscription
    {
        return Subscription::create([
            'opportunity_id' => $opportunity->id,
            'client_id'      => $opportunity->client_id,
            'monthly_rate'   => $this->getOpportunityAmount($opportunity),
            'start_date'     => now(),
            'end_date'       => now()->addYear(),
            'status'         => 'active',
        ]);
    }

    /**
     * Create an Invoice record linked to the won opportunity.
     */
    protected function createInvoice(Opportunity $opportunity): Invoice
    {
        return Invoice::create([
            'invoice_number' => 'INV-' . strtoupper(uniqid()),
            'client_id'      => $opportunity->client_id,
            'amount'         => $this->getOpportunityAmount($opportunity),
            'due_date'       => now()->addDays(30),
            'status'         => 'draft',
            'notes'          => 'Generated from Opportunity: ' . $opportunity->title,
        ]);
    }

    /**
     * Get the opportunity amount.
     */
    protected function getOpportunityAmount(Opportunity $opportunity): float
    {
        return (float) ($opportunity->estimated_value ?? $opportunity->final_value ?? 0);
    }
}
