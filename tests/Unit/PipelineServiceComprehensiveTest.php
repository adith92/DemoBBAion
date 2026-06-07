<?php

namespace Tests\Unit;

use App\Services\PipelineService;
use Tests\TestCase;

class PipelineServiceComprehensiveTest extends TestCase
{
    protected PipelineService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PipelineService();
    }

    // =========================================================================
    // getNextStages Tests
    // =========================================================================

    public function test_get_next_stages_from_prospecting()
    {
        $stages = $this->service->getNextStages('prospecting');

        expect($stages)->toBe(['proposal', 'negotiation', 'won', 'lost']);
    }

    public function test_get_next_stages_from_qualification()
    {
        $stages = $this->service->getNextStages('qualification');

        expect($stages)->toBe(['prospecting', 'proposal', 'negotiation', 'won', 'lost']);
    }

    public function test_get_next_stages_from_proposal()
    {
        $stages = $this->service->getNextStages('proposal');

        expect($stages)->toBe(['prospecting', 'negotiation', 'won', 'lost']);
    }

    public function test_get_next_stages_from_negotiation()
    {
        $stages = $this->service->getNextStages('negotiation');

        expect($stages)->toBe(['prospecting', 'proposal', 'won', 'lost']);
    }

    public function test_get_next_stages_from_won()
    {
        $stages = $this->service->getNextStages('won');

        expect($stages)->toBe(['prospecting', 'proposal', 'negotiation', 'lost']);
    }

    public function test_get_next_stages_from_lost()
    {
        $stages = $this->service->getNextStages('lost');

        expect($stages)->toBe(['prospecting', 'proposal', 'negotiation', 'won']);
    }

    public function test_get_next_stages_from_closed()
    {
        $stages = $this->service->getNextStages('closed');

        expect($stages)->toBe(['prospecting', 'proposal', 'negotiation', 'won', 'lost']);
    }

    public function test_get_next_stages_from_invalid_stage_returns_empty()
    {
        $stages = $this->service->getNextStages('invalid_stage');

        expect($stages)->toEqual([]);
    }

    public function test_get_next_stages_is_case_insensitive()
    {
        $stages1 = $this->service->getNextStages('PROSPECTING');
        $stages2 = $this->service->getNextStages('Prospecting');
        $stages3 = $this->service->getNextStages('prospecting');

        expect($stages1)->toBe($stages2);
        expect($stages2)->toBe($stages3);
    }

    // =========================================================================
    // canTransition Tests
    // =========================================================================

    public function test_cannot_transition_prospecting_to_qualification()
    {
        // qualification is NOT in allStages, but has a backward-compat entry
        // prospecting's transitions don't include qualification
        $result = $this->service->canTransition('prospecting', 'qualification');

        expect($result)->toBeFalse();
    }

    public function test_can_transition_prospecting_to_lost()
    {
        $result = $this->service->canTransition('prospecting', 'lost');

        expect($result)->toBeTrue();
    }

    public function test_can_transition_prospecting_to_proposal()
    {
        $result = $this->service->canTransition('prospecting', 'proposal');

        expect($result)->toBeTrue();
    }

    public function test_can_transition_prospecting_to_negotiation()
    {
        $result = $this->service->canTransition('prospecting', 'negotiation');

        expect($result)->toBeTrue();
    }

    public function test_can_transition_qualification_to_proposal()
    {
        $result = $this->service->canTransition('qualification', 'proposal');

        expect($result)->toBeTrue();
    }

    public function test_can_transition_qualification_to_prospecting()
    {
        // Free movement: qualification can go back to prospecting
        $result = $this->service->canTransition('qualification', 'prospecting');

        expect($result)->toBeTrue();
    }

    public function test_can_transition_proposal_to_negotiation()
    {
        $result = $this->service->canTransition('proposal', 'negotiation');

        expect($result)->toBeTrue();
    }

    public function test_can_transition_negotiation_to_won()
    {
        $result = $this->service->canTransition('negotiation', 'won');

        expect($result)->toBeTrue();
    }

    public function test_can_transition_from_won()
    {
        // Free movement: won is NOT a final state
        expect($this->service->canTransition('won', 'lost'))->toBeTrue();
        expect($this->service->canTransition('won', 'negotiation'))->toBeTrue();
        expect($this->service->canTransition('won', 'prospecting'))->toBeTrue();
    }

    public function test_can_transition_from_lost()
    {
        // Free movement: lost is NOT a final state
        expect($this->service->canTransition('lost', 'won'))->toBeTrue();
        expect($this->service->canTransition('lost', 'negotiation'))->toBeTrue();
    }

    public function test_transition_is_case_insensitive()
    {
        // Use stages that exist in transitions: prospecting → proposal
        $result1 = $this->service->canTransition('PROSPECTING', 'PROPOSAL');
        $result2 = $this->service->canTransition('Prospecting', 'Proposal');
        $result3 = $this->service->canTransition('prospecting', 'proposal');

        expect($result1)->toBeTrue();
        expect($result2)->toBeTrue();
        expect($result3)->toBeTrue();
    }

    public function test_can_transition_from_qualification_to_lost()
    {
        $result = $this->service->canTransition('qualification', 'lost');

        expect($result)->toBeTrue();
    }

    public function test_can_transition_from_proposal_to_lost()
    {
        $result = $this->service->canTransition('proposal', 'lost');

        expect($result)->toBeTrue();
    }

    public function test_can_transition_from_negotiation_to_lost()
    {
        $result = $this->service->canTransition('negotiation', 'lost');

        expect($result)->toBeTrue();
    }

    // =========================================================================
    // Complex Transition Chains
    // =========================================================================

    public function test_forward_progression_prospecting_to_won()
    {
        // Free movement: can go directly or step by step using valid stages
        expect($this->service->canTransition('prospecting', 'proposal'))->toBeTrue();
        expect($this->service->canTransition('proposal', 'negotiation'))->toBeTrue();
        expect($this->service->canTransition('negotiation', 'won'))->toBeTrue();
    }

    public function test_lost_is_accessible_from_multiple_stages()
    {
        expect($this->service->canTransition('prospecting', 'lost'))->toBeTrue();
        expect($this->service->canTransition('qualification', 'lost'))->toBeTrue();
        expect($this->service->canTransition('proposal', 'lost'))->toBeTrue();
        expect($this->service->canTransition('negotiation', 'lost'))->toBeTrue();
    }

    public function test_can_skip_stages_in_free_movement()
    {
        // Kanban free movement: skipping stages IS allowed
        expect($this->service->canTransition('prospecting', 'proposal'))->toBeTrue();
        expect($this->service->canTransition('prospecting', 'negotiation'))->toBeTrue();
        expect($this->service->canTransition('prospecting', 'won'))->toBeTrue();
        expect($this->service->canTransition('qualification', 'negotiation'))->toBeTrue();
        expect($this->service->canTransition('qualification', 'won'))->toBeTrue();
        expect($this->service->canTransition('proposal', 'won'))->toBeTrue();
    }

    public function test_can_go_backward()
    {
        // Kanban free movement: backward IS allowed
        expect($this->service->canTransition('qualification', 'prospecting'))->toBeTrue();
        expect($this->service->canTransition('negotiation', 'proposal'))->toBeTrue();
        expect($this->service->canTransition('proposal', 'prospecting'))->toBeTrue();
    }

    public function test_won_and_lost_are_not_final_states()
    {
        // Won has valid next states in free movement
        $wonStages = $this->service->getNextStages('won');
        expect($wonStages)->toBe(['prospecting', 'proposal', 'negotiation', 'lost']);

        // Lost has valid next states in free movement
        $lostStages = $this->service->getNextStages('lost');
        expect($lostStages)->toBe(['prospecting', 'proposal', 'negotiation', 'won']);
    }

    // =========================================================================
    // Edge Cases
    // =========================================================================

    public function test_empty_string_stage_returns_empty()
    {
        $stages = $this->service->getNextStages('');
        expect($stages)->toEqual([]);
    }

    public function test_whitespace_stage_returns_empty()
    {
        $stages = $this->service->getNextStages('   ');
        expect($stages)->toEqual([]);
    }

    public function test_special_characters_in_stage_returns_empty()
    {
        $stages = $this->service->getNextStages('pro@specting!');
        expect($stages)->toEqual([]);
    }

    public function test_null_stage_handled_gracefully()
    {
        // Should not throw error
        $stages = $this->service->getNextStages('null_value');
        expect(is_array($stages))->toBeTrue();
    }

    // =========================================================================
    // Transition Matrix Validation
    // =========================================================================

    public function test_all_valid_transitions_defined()
    {
        // Free movement kanban: every stage can reach every other stage
        $reachableFromProspecting = ['proposal', 'negotiation', 'won', 'lost'];
        $reachableFromQualification = ['prospecting', 'proposal', 'negotiation', 'won', 'lost'];
        $reachableFromProposal = ['prospecting', 'negotiation', 'won', 'lost'];
        $reachableFromNegotiation = ['prospecting', 'proposal', 'won', 'lost'];

        expect($this->service->getNextStages('prospecting'))->toBe($reachableFromProspecting);
        expect($this->service->getNextStages('qualification'))->toBe($reachableFromQualification);
        expect($this->service->getNextStages('proposal'))->toBe($reachableFromProposal);
        expect($this->service->getNextStages('negotiation'))->toBe($reachableFromNegotiation);
    }

    public function test_early_exit_paths()
    {
        // Can exit from any stage via 'lost'
        expect($this->service->canTransition('prospecting', 'lost'))->toBeTrue();
        expect($this->service->canTransition('qualification', 'lost'))->toBeTrue();
        expect($this->service->canTransition('proposal', 'lost'))->toBeTrue();
        expect($this->service->canTransition('negotiation', 'lost'))->toBeTrue();
    }

    public function test_all_stages_can_reach_won()
    {
        // Free movement: all stages can now transition to won
        expect($this->service->canTransition('prospecting', 'won'))->toBeTrue();
        expect($this->service->canTransition('qualification', 'won'))->toBeTrue();
        expect($this->service->canTransition('proposal', 'won'))->toBeTrue();
        expect($this->service->canTransition('negotiation', 'won'))->toBeTrue();
    }

}
