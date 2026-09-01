<?php

namespace Tests\Feature;

use App\Models\Checkpoint;
use App\Models\Idea;
use App\Models\IdeaFile;
use App\Models\IdeaLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The security backbone of the application: a signed-in user must not be able
 * to reach another user's idea, or anything hanging off it, by editing an ID in
 * a URL or hand-crafting a request.
 */
class IdeaAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $intruder;

    protected Idea $idea;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $this->intruder = User::factory()->create();
        $this->idea = Idea::factory()->for($this->owner)->create();
    }

    /*
    |--------------------------------------------------------------------------
    | Ideas
    |--------------------------------------------------------------------------
    */

    public function test_a_user_cannot_view_another_users_idea(): void
    {
        $this->actingAs($this->intruder)
            ->get(route('ideas.show', $this->idea))
            ->assertForbidden();
    }

    public function test_a_user_cannot_open_the_edit_form_for_another_users_idea(): void
    {
        $this->actingAs($this->intruder)
            ->get(route('ideas.edit', $this->idea))
            ->assertForbidden();
    }

    public function test_a_user_cannot_update_another_users_idea(): void
    {
        $this->actingAs($this->intruder)
            ->patch(route('ideas.update', $this->idea), [
                'title' => 'Hijacked',
                'description' => 'Should never be written.',
                'status' => 'pending',
                'priority' => 'low',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('ideas', ['id' => $this->idea->id, 'title' => 'Hijacked']);
    }

    public function test_a_user_cannot_delete_another_users_idea(): void
    {
        $this->actingAs($this->intruder)
            ->delete(route('ideas.destroy', $this->idea))
            ->assertForbidden();

        $this->assertDatabaseHas('ideas', ['id' => $this->idea->id]);
    }

    public function test_a_user_cannot_archive_or_restore_another_users_idea(): void
    {
        $this->actingAs($this->intruder)
            ->patch(route('ideas.archive', $this->idea))
            ->assertForbidden();

        $this->assertNull($this->idea->fresh()->archived_at);

        $archived = Idea::factory()->for($this->owner)->archived()->create();

        $this->actingAs($this->intruder)
            ->patch(route('ideas.restore', $archived))
            ->assertForbidden();

        $this->assertNotNull($archived->fresh()->archived_at);
    }

    public function test_a_user_cannot_load_another_users_cover_image(): void
    {
        Storage::fake('local');

        $idea = Idea::factory()->for($this->owner)->create([
            'cover_image' => UploadedFile::fake()->image('cover.jpg')->store('idea-covers/1', 'local'),
        ]);

        $this->actingAs($this->intruder)
            ->get(route('ideas.cover', $idea))
            ->assertForbidden();

        $this->actingAs($this->owner)
            ->get(route('ideas.cover', $idea))
            ->assertOk();
    }

    public function test_the_index_only_lists_the_signed_in_users_ideas(): void
    {
        $mine = Idea::factory()->for($this->intruder)->create(['title' => 'My own idea']);

        $this->actingAs($this->intruder)
            ->get(route('ideas.index'))
            ->assertOk()
            ->assertSee($mine->title)
            ->assertDontSee($this->idea->title);
    }

    /*
    |--------------------------------------------------------------------------
    | Checkpoints
    |--------------------------------------------------------------------------
    */

    public function test_a_user_cannot_add_a_checkpoint_to_another_users_idea(): void
    {
        $this->actingAs($this->intruder)
            ->post(route('checkpoints.store', $this->idea), ['title' => 'Sneaky step'])
            ->assertForbidden();

        $this->assertDatabaseCount('checkpoints', 0);
    }

    public function test_a_user_cannot_toggle_update_or_delete_another_users_checkpoint(): void
    {
        $checkpoint = Checkpoint::factory()->for($this->idea)->create();

        $this->actingAs($this->intruder)
            ->patch(route('checkpoints.toggle', $checkpoint))
            ->assertForbidden();

        $this->assertFalse($checkpoint->fresh()->is_completed);

        $this->actingAs($this->intruder)
            ->patch(route('checkpoints.update', $checkpoint), ['title' => 'Renamed'])
            ->assertForbidden();

        $this->actingAs($this->intruder)
            ->delete(route('checkpoints.destroy', $checkpoint))
            ->assertForbidden();

        $this->assertDatabaseHas('checkpoints', ['id' => $checkpoint->id]);
    }

    /*
    |--------------------------------------------------------------------------
    | Links
    |--------------------------------------------------------------------------
    */

    public function test_a_user_cannot_manage_links_on_another_users_idea(): void
    {
        $link = IdeaLink::factory()->for($this->idea)->create();

        $this->actingAs($this->intruder)
            ->post(route('links.store', $this->idea), ['label' => 'X', 'url' => 'https://example.com'])
            ->assertForbidden();

        $this->actingAs($this->intruder)
            ->patch(route('links.update', $link), ['label' => 'Y', 'url' => 'https://evil.test'])
            ->assertForbidden();

        $this->actingAs($this->intruder)
            ->delete(route('links.destroy', $link))
            ->assertForbidden();

        $this->assertDatabaseHas('idea_links', ['id' => $link->id, 'label' => $link->label]);
    }

    /*
    |--------------------------------------------------------------------------
    | Files
    |--------------------------------------------------------------------------
    */

    public function test_a_user_cannot_download_another_users_file(): void
    {
        Storage::fake('local');

        $path = UploadedFile::fake()->create('secret.pdf', 100)->store('idea-files/x', 'local');
        $file = IdeaFile::factory()->for($this->idea)->create(['path' => $path]);

        $this->actingAs($this->intruder)
            ->get(route('files.show', $file))
            ->assertForbidden();

        $this->actingAs($this->owner)
            ->get(route('files.show', $file))
            ->assertOk();
    }

    public function test_a_user_cannot_upload_to_or_delete_files_on_another_users_idea(): void
    {
        Storage::fake('local');

        $file = IdeaFile::factory()->for($this->idea)->create();

        $this->actingAs($this->intruder)
            ->post(route('files.store', $this->idea), [
                'files' => [UploadedFile::fake()->create('payload.pdf', 10)],
            ])
            ->assertForbidden();

        $this->actingAs($this->intruder)
            ->delete(route('files.destroy', $file))
            ->assertForbidden();

        $this->assertDatabaseHas('idea_files', ['id' => $file->id]);
        $this->assertDatabaseCount('idea_files', 1);
    }

    /*
    |--------------------------------------------------------------------------
    | Guests
    |--------------------------------------------------------------------------
    */

    public function test_guests_are_redirected_to_login_from_every_protected_route(): void
    {
        $checkpoint = Checkpoint::factory()->for($this->idea)->create();
        $link = IdeaLink::factory()->for($this->idea)->create();
        $file = IdeaFile::factory()->for($this->idea)->create();

        $routes = [
            route('dashboard'),
            route('ideas.index'),
            route('ideas.create'),
            route('ideas.show', $this->idea),
            route('ideas.edit', $this->idea),
            route('ideas.cover', $this->idea),
            route('files.show', $file),
            route('profile.edit'),
            route('profile.photo'),
        ];

        foreach ($routes as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }

        $this->post(route('checkpoints.store', $this->idea))->assertRedirect(route('login'));
        $this->patch(route('checkpoints.toggle', $checkpoint))->assertRedirect(route('login'));
        $this->delete(route('links.destroy', $link))->assertRedirect(route('login'));
    }
}
