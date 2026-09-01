<?php

namespace Tests\Feature;

use App\Enums\IdeaStatus;
use App\Models\Checkpoint;
use App\Models\Idea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckpointProgressTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_an_idea_with_no_checkpoints_reports_zero_progress(): void
    {
        $idea = Idea::factory()->for($this->user)->status(IdeaStatus::Pending)->create();

        $this->assertSame(0, $idea->progress);
        $this->assertSame(0, $idea->totalCheckpoints());
    }

    public function test_a_completed_idea_with_no_checkpoints_reports_full_progress(): void
    {
        $idea = Idea::factory()->for($this->user)->status(IdeaStatus::Completed)->create();

        $this->assertSame(100, $idea->progress);
    }

    public function test_progress_is_the_percentage_of_completed_checkpoints(): void
    {
        $idea = Idea::factory()->for($this->user)->status(IdeaStatus::InProgress)->create();

        Checkpoint::factory()->for($idea)->completed()->count(6)->create();
        Checkpoint::factory()->for($idea)->count(4)->create();

        // 6 of 10 completed.
        $this->assertSame(60, $idea->fresh()->progress);
    }

    public function test_progress_reaches_one_hundred_when_every_checkpoint_is_done(): void
    {
        $idea = Idea::factory()->for($this->user)->status(IdeaStatus::InProgress)->create();
        Checkpoint::factory()->for($idea)->completed()->count(3)->create();

        $this->assertSame(100, $idea->fresh()->progress);
    }

    public function test_progress_is_identical_whether_counts_are_eager_loaded_or_not(): void
    {
        $idea = Idea::factory()->for($this->user)->create();
        Checkpoint::factory()->for($idea)->completed()->count(1)->create();
        Checkpoint::factory()->for($idea)->count(2)->create();

        $eager = Idea::withProgress()->find($idea->id);

        $this->assertSame(33, $idea->fresh()->progress);
        $this->assertSame(33, $eager->progress);
    }

    public function test_a_user_can_add_a_checkpoint(): void
    {
        $idea = Idea::factory()->for($this->user)->create();

        $this->actingAs($this->user)
            ->post(route('checkpoints.store', $idea), ['title' => 'Sketch the layout'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('checkpoints', [
            'idea_id' => $idea->id,
            'title' => 'Sketch the layout',
            'is_completed' => false,
        ]);
    }

    public function test_checkpoint_titles_are_validated(): void
    {
        $idea = Idea::factory()->for($this->user)->create();

        $this->actingAs($this->user)
            ->post(route('checkpoints.store', $idea), ['title' => ''])
            ->assertSessionHasErrors('title');

        $this->assertDatabaseCount('checkpoints', 0);
    }

    public function test_toggling_a_checkpoint_persists_the_state_and_the_timestamp(): void
    {
        $idea = Idea::factory()->for($this->user)->create();
        $checkpoint = Checkpoint::factory()->for($idea)->create();

        $this->actingAs($this->user)
            ->patch(route('checkpoints.toggle', $checkpoint))
            ->assertRedirect();

        $checkpoint->refresh();
        $this->assertTrue($checkpoint->is_completed);
        $this->assertNotNull($checkpoint->completed_at);

        // Toggling back clears both.
        $this->actingAs($this->user)->patch(route('checkpoints.toggle', $checkpoint));

        $checkpoint->refresh();
        $this->assertFalse($checkpoint->is_completed);
        $this->assertNull($checkpoint->completed_at);
    }

    public function test_toggling_a_checkpoint_records_activity(): void
    {
        $idea = Idea::factory()->for($this->user)->create();
        $checkpoint = Checkpoint::factory()->for($idea)->create(['title' => 'Order the parts']);

        $this->actingAs($this->user)->patch(route('checkpoints.toggle', $checkpoint));

        $this->assertDatabaseHas('idea_activities', [
            'idea_id' => $idea->id,
            'type' => 'checkpoint',
            'description' => 'Completed checkpoint: Order the parts',
        ]);
    }

    public function test_a_user_can_rename_and_delete_their_own_checkpoint(): void
    {
        $idea = Idea::factory()->for($this->user)->create();
        $checkpoint = Checkpoint::factory()->for($idea)->create();

        $this->actingAs($this->user)
            ->patch(route('checkpoints.update', $checkpoint), ['title' => 'Renamed step'])
            ->assertSessionHas('success');

        $this->assertSame('Renamed step', $checkpoint->fresh()->title);

        $this->actingAs($this->user)
            ->delete(route('checkpoints.destroy', $checkpoint))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('checkpoints', 0);
    }
}
