<?php

namespace Tests\Feature;

use App\Enums\IdeaPriority;
use App\Enums\IdeaStatus;
use App\Models\Idea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IdeaManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_a_user_can_create_an_idea(): void
    {
        $this->actingAs($this->user)
            ->post(route('ideas.store'), [
                'title' => 'Build a greenhouse',
                'description' => 'A small lean-to greenhouse against the south wall.',
                'status' => IdeaStatus::Pending->value,
                'priority' => IdeaPriority::High->value,
                'category' => 'Home',
                'tags' => 'garden, diy, garden',
                'due_date' => now()->addMonth()->format('Y-m-d'),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $idea = Idea::firstWhere('title', 'Build a greenhouse');

        $this->assertNotNull($idea);
        $this->assertSame($this->user->id, $idea->user_id);
        $this->assertSame(IdeaPriority::High, $idea->priority);
        // Tags are trimmed and de-duplicated on the way in.
        $this->assertSame(['garden', 'diy'], $idea->tags);
    }

    public function test_the_owner_cannot_be_overridden_through_the_request(): void
    {
        $victim = User::factory()->create();

        $this->actingAs($this->user)->post(route('ideas.store'), [
            'user_id' => $victim->id,
            'title' => 'Ownership test',
            'description' => 'Attempting to assign this to somebody else.',
            'status' => IdeaStatus::Pending->value,
            'priority' => IdeaPriority::Low->value,
        ]);

        $idea = Idea::firstWhere('title', 'Ownership test');

        $this->assertSame($this->user->id, $idea->user_id);
    }

    public function test_creating_an_idea_requires_valid_input(): void
    {
        $this->actingAs($this->user)
            ->post(route('ideas.store'), [
                'title' => 'ab',
                'description' => '',
                'status' => 'not-a-status',
                'priority' => 'urgent',
            ])
            ->assertSessionHasErrors(['title', 'description', 'status', 'priority']);

        $this->assertDatabaseCount('ideas', 0);
    }

    public function test_a_user_can_update_their_own_idea_and_a_status_change_is_recorded(): void
    {
        $idea = Idea::factory()->for($this->user)->status(IdeaStatus::Pending)->create();

        $this->actingAs($this->user)
            ->patch(route('ideas.update', $idea), [
                'title' => 'Updated title',
                'description' => 'Updated description text.',
                'status' => IdeaStatus::Completed->value,
                'priority' => IdeaPriority::Low->value,
            ])
            ->assertRedirect(route('ideas.show', $idea))
            ->assertSessionHas('success');

        $this->assertSame('Updated title', $idea->fresh()->title);

        $this->assertDatabaseHas('idea_activities', [
            'idea_id' => $idea->id,
            'type' => 'status',
        ]);
    }

    public function test_a_user_can_archive_and_restore_an_idea(): void
    {
        $idea = Idea::factory()->for($this->user)->create();

        $this->actingAs($this->user)->patch(route('ideas.archive', $idea));
        $this->assertNotNull($idea->fresh()->archived_at);

        // Archived ideas drop out of the active list...
        // (asserted on the query result rather than the markup, because the
        // archive confirmation flash also mentions the title)
        $active = $this->actingAs($this->user)->get(route('ideas.index'));
        $this->assertCount(0, $active->viewData('ideas')->items());

        // ...and appear under the archived view.
        $archived = $this->actingAs($this->user)->get(route('ideas.index', ['view' => 'archived']));
        $this->assertCount(1, $archived->viewData('ideas')->items());
        $this->assertSame($idea->id, $archived->viewData('ideas')->items()[0]->id);

        $this->actingAs($this->user)->patch(route('ideas.restore', $idea));
        $this->assertNull($idea->fresh()->archived_at);
    }

    public function test_deleting_an_idea_removes_its_children_and_stored_files(): void
    {
        Storage::fake('local');

        $idea = Idea::factory()->for($this->user)->create([
            'cover_image' => UploadedFile::fake()->image('cover.jpg')->store('idea-covers/1', 'local'),
        ]);

        $path = UploadedFile::fake()->create('doc.pdf', 20)->store('idea-files/1', 'local');
        $idea->files()->create([
            'disk' => 'local',
            'path' => $path,
            'original_name' => 'doc.pdf',
            'mime_type' => 'application/pdf',
            'size' => 20,
        ]);
        $idea->checkpoints()->create(['title' => 'Step one']);

        Storage::disk('local')->assertExists($path);

        $this->actingAs($this->user)
            ->delete(route('ideas.destroy', $idea))
            ->assertRedirect(route('ideas.index'));

        $this->assertDatabaseCount('ideas', 0);
        $this->assertDatabaseCount('checkpoints', 0);
        $this->assertDatabaseCount('idea_files', 0);

        // No orphaned files are left behind on disk.
        Storage::disk('local')->assertMissing($path);
        Storage::disk('local')->assertMissing($idea->cover_image);
    }

    public function test_replacing_a_cover_image_deletes_the_previous_file(): void
    {
        Storage::fake('local');

        $original = UploadedFile::fake()->image('first.jpg')->store('idea-covers/1', 'local');
        $idea = Idea::factory()->for($this->user)->create(['cover_image' => $original]);

        $this->actingAs($this->user)->patch(route('ideas.update', $idea), [
            'title' => $idea->title,
            'description' => $idea->description,
            'status' => $idea->status->value,
            'priority' => $idea->priority->value,
            'cover_image' => UploadedFile::fake()->image('second.jpg'),
        ]);

        Storage::disk('local')->assertMissing($original);
        Storage::disk('local')->assertExists($idea->fresh()->cover_image);
    }

    public function test_an_oversized_or_non_image_cover_is_rejected(): void
    {
        Storage::fake('local');

        $this->actingAs($this->user)
            ->post(route('ideas.store'), [
                'title' => 'Cover validation',
                'description' => 'Checking the upload rules.',
                'status' => IdeaStatus::Pending->value,
                'priority' => IdeaPriority::Low->value,
                'cover_image' => UploadedFile::fake()->create('virus.exe', 100),
            ])
            ->assertSessionHasErrors('cover_image');

        $this->actingAs($this->user)
            ->post(route('ideas.store'), [
                'title' => 'Cover size',
                'description' => 'Checking the size rule.',
                'status' => IdeaStatus::Pending->value,
                'priority' => IdeaPriority::Low->value,
                // 5 MB, over the 4 MB cap.
                'cover_image' => UploadedFile::fake()->image('huge.jpg')->size(5120),
            ])
            ->assertSessionHasErrors('cover_image');

        $this->assertDatabaseCount('ideas', 0);
    }

    public function test_ideas_can_be_searched_filtered_and_sorted(): void
    {
        Idea::factory()->for($this->user)->create([
            'title' => 'Hydroponics rig',
            'status' => IdeaStatus::Completed,
            'priority' => IdeaPriority::High,
            'category' => 'Garden',
        ]);

        Idea::factory()->for($this->user)->create([
            'title' => 'Rewrite the newsletter',
            'status' => IdeaStatus::Pending,
            'priority' => IdeaPriority::Low,
            'category' => 'Writing',
        ]);

        $this->actingAs($this->user)
            ->get(route('ideas.index', ['search' => 'Hydroponics']))
            ->assertSee('Hydroponics rig')
            ->assertDontSee('Rewrite the newsletter');

        $this->actingAs($this->user)
            ->get(route('ideas.index', ['status' => IdeaStatus::Pending->value]))
            ->assertSee('Rewrite the newsletter')
            ->assertDontSee('Hydroponics rig');

        $this->actingAs($this->user)
            ->get(route('ideas.index', ['category' => 'Garden']))
            ->assertSee('Hydroponics rig')
            ->assertDontSee('Rewrite the newsletter');

        // Unrecognised sort/filter values fall back rather than breaking the query.
        $this->actingAs($this->user)
            ->get(route('ideas.index', ['sort' => 'drop table', 'priority' => 'bogus']))
            ->assertOk();
    }

    public function test_the_ideas_list_is_paginated(): void
    {
        Idea::factory()->for($this->user)->count(12)->create();

        $response = $this->actingAs($this->user)->get(route('ideas.index'));

        $response->assertOk();
        $this->assertCount(9, $response->viewData('ideas')->items());
        $this->assertSame(2, $response->viewData('ideas')->lastPage());
    }

    public function test_the_dashboard_renders_for_a_user_with_no_ideas(): void
    {
        $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('No ideas yet');
    }
}
