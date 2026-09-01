<?php

namespace Tests\Feature;

use App\Enums\IdeaPriority;
use App\Enums\IdeaStatus;
use App\Models\Checkpoint;
use App\Models\Idea;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Status is derived from checkpoint completion, server-side, whenever a
 * checkpoint changes. Progress and status must never disagree.
 */
class IdeaStatusAutomationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * @return array{0: Idea, 1: Collection}
     */
    protected function ideaWithCheckpoints(int $count = 5): array
    {
        $idea = Idea::factory()->for($this->user)->status(IdeaStatus::Pending)->create();
        $checkpoints = Checkpoint::factory()->for($idea)->count($count)->create();

        return [$idea->fresh(), $checkpoints];
    }

    protected function complete(Checkpoint $checkpoint): void
    {
        $this->actingAs($this->user)->patch(route('checkpoints.toggle', $checkpoint));
    }

    public function test_a_new_idea_is_pending(): void
    {
        $this->actingAs($this->user)->post(route('ideas.store'), [
            'title' => 'A fresh idea',
            'description' => 'Newly created, so nothing is done yet.',
            // Even an explicit Completed is ignored on creation.
            'status' => IdeaStatus::Completed->value,
            'priority' => IdeaPriority::Medium->value,
        ]);

        $idea = Idea::firstWhere('title', 'A fresh idea');

        $this->assertSame(IdeaStatus::Pending, $idea->status);
        $this->assertSame(0, $idea->progress);
    }

    public function test_zero_of_five_is_pending_at_zero_percent(): void
    {
        [$idea] = $this->ideaWithCheckpoints(5);

        $this->assertSame(IdeaStatus::Pending, $idea->status);
        $this->assertSame(0, $idea->progress);
    }

    public function test_two_of_five_is_in_progress_at_forty_percent(): void
    {
        [$idea, $checkpoints] = $this->ideaWithCheckpoints(5);

        $this->complete($checkpoints[0]);
        $this->complete($checkpoints[1]);

        $idea->refresh();

        $this->assertSame(IdeaStatus::InProgress, $idea->status);
        $this->assertSame(40, $idea->progress);
    }

    public function test_five_of_five_is_completed_at_one_hundred_percent(): void
    {
        [$idea, $checkpoints] = $this->ideaWithCheckpoints(5);

        foreach ($checkpoints as $checkpoint) {
            $this->complete($checkpoint);
        }

        $idea->refresh();

        $this->assertSame(IdeaStatus::Completed, $idea->status);
        $this->assertSame(100, $idea->progress);
    }

    public function test_unchecking_one_completed_checkpoint_returns_the_idea_to_in_progress(): void
    {
        [$idea, $checkpoints] = $this->ideaWithCheckpoints(5);

        foreach ($checkpoints as $checkpoint) {
            $this->complete($checkpoint);
        }

        $this->assertSame(IdeaStatus::Completed, $idea->fresh()->status);

        // Reopen one of them.
        $this->complete($checkpoints[2]);

        $idea->refresh();

        $this->assertSame(IdeaStatus::InProgress, $idea->status);
        $this->assertSame(80, $idea->progress);
    }

    public function test_unchecking_every_checkpoint_returns_the_idea_to_pending(): void
    {
        [$idea, $checkpoints] = $this->ideaWithCheckpoints(3);

        foreach ($checkpoints as $checkpoint) {
            $this->complete($checkpoint);
        }
        $this->assertSame(IdeaStatus::Completed, $idea->fresh()->status);

        foreach ($checkpoints as $checkpoint) {
            $this->complete($checkpoint);
        }

        $idea->refresh();

        $this->assertSame(IdeaStatus::Pending, $idea->status);
        $this->assertSame(0, $idea->progress);
    }

    public function test_adding_a_checkpoint_to_a_finished_idea_reopens_it(): void
    {
        [$idea, $checkpoints] = $this->ideaWithCheckpoints(2);

        foreach ($checkpoints as $checkpoint) {
            $this->complete($checkpoint);
        }
        $this->assertSame(IdeaStatus::Completed, $idea->fresh()->status);

        $this->actingAs($this->user)
            ->post(route('checkpoints.store', $idea), ['title' => 'One more thing']);

        $idea->refresh();

        $this->assertSame(IdeaStatus::InProgress, $idea->status);
        $this->assertSame(67, $idea->progress);
    }

    public function test_deleting_the_last_outstanding_checkpoint_completes_the_idea(): void
    {
        [$idea, $checkpoints] = $this->ideaWithCheckpoints(3);

        $this->complete($checkpoints[0]);
        $this->complete($checkpoints[1]);
        $this->assertSame(IdeaStatus::InProgress, $idea->fresh()->status);

        $this->actingAs($this->user)
            ->delete(route('checkpoints.destroy', $checkpoints[2]));

        $idea->refresh();

        $this->assertSame(IdeaStatus::Completed, $idea->status);
        $this->assertSame(100, $idea->progress);
    }

    /**
     * With no checkpoints there is nothing to derive from, so the owner keeps
     * manual control and the idea is not trapped at Pending forever.
     */
    public function test_an_idea_without_checkpoints_keeps_a_manually_chosen_status(): void
    {
        $idea = Idea::factory()->for($this->user)->status(IdeaStatus::Pending)->create();

        $this->actingAs($this->user)->patch(route('ideas.update', $idea), [
            'title' => $idea->title,
            'description' => $idea->description,
            'status' => IdeaStatus::Completed->value,
            'priority' => $idea->priority->value,
        ]);

        $this->assertSame(IdeaStatus::Completed, $idea->fresh()->status);
    }

    /**
     * The headline server-side guarantee: once checkpoints exist, a status
     * posted by hand is discarded rather than trusted.
     */
    public function test_a_submitted_status_is_ignored_once_the_idea_has_checkpoints(): void
    {
        [$idea, $checkpoints] = $this->ideaWithCheckpoints(4);

        $this->complete($checkpoints[0]);
        $this->assertSame(IdeaStatus::InProgress, $idea->fresh()->status);

        $this->actingAs($this->user)->patch(route('ideas.update', $idea), [
            'title' => $idea->title,
            'description' => $idea->description,
            'status' => IdeaStatus::Completed->value,
            'priority' => $idea->priority->value,
        ]);

        $idea->refresh();

        // Still 1 of 4 done, so still In Progress despite what was submitted.
        $this->assertSame(IdeaStatus::InProgress, $idea->status);
        $this->assertSame(25, $idea->progress);
    }

    public function test_status_changes_are_recorded_in_the_activity_history(): void
    {
        [$idea, $checkpoints] = $this->ideaWithCheckpoints(2);

        $this->complete($checkpoints[0]);

        $this->assertDatabaseHas('idea_activities', [
            'idea_id' => $idea->id,
            'type' => 'status',
            'description' => 'Status changed from Pending to In Progress (from checkpoint progress)',
        ]);
    }

    /**
     * Guards against status and progress drifting apart across a full run
     * up and back down again.
     */
    public function test_status_and_progress_stay_consistent_across_every_transition(): void
    {
        [$idea, $checkpoints] = $this->ideaWithCheckpoints(4);

        $expected = [
            1 => [IdeaStatus::InProgress, 25],
            2 => [IdeaStatus::InProgress, 50],
            3 => [IdeaStatus::InProgress, 75],
            4 => [IdeaStatus::Completed, 100],
        ];

        foreach ($checkpoints as $i => $checkpoint) {
            $this->complete($checkpoint);
            $idea->refresh();

            [$status, $progress] = $expected[$i + 1];
            $this->assertSame($status, $idea->status, 'after completing '.($i + 1));
            $this->assertSame($progress, $idea->progress, 'after completing '.($i + 1));
        }

        // ...and back down.
        foreach (array_reverse($checkpoints->all()) as $checkpoint) {
            $this->complete($checkpoint);
        }

        $idea->refresh();
        $this->assertSame(IdeaStatus::Pending, $idea->status);
        $this->assertSame(0, $idea->progress);
    }
}
