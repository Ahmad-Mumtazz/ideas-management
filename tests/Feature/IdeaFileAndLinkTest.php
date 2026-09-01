<?php

namespace Tests\Feature;

use App\Models\Idea;
use App\Models\IdeaFile;
use App\Models\IdeaLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IdeaFileAndLinkTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Idea $idea;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->idea = Idea::factory()->for($this->user)->create();
    }

    /*
    |--------------------------------------------------------------------------
    | Files
    |--------------------------------------------------------------------------
    */

    public function test_a_user_can_upload_multiple_files_to_their_idea(): void
    {
        Storage::fake('local');

        $this->actingAs($this->user)
            ->post(route('files.store', $this->idea), [
                'files' => [
                    UploadedFile::fake()->create('spec.pdf', 120, 'application/pdf'),
                    UploadedFile::fake()->create('budget.xlsx', 80),
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseCount('idea_files', 2);

        foreach (IdeaFile::all() as $file) {
            Storage::disk('local')->assertExists($file->path);
            // Files are stored under the private disk, never the public one.
            $this->assertStringStartsWith('idea-files/', $file->path);
        }
    }

    public function test_uploads_are_stored_under_a_generated_name_not_the_client_name(): void
    {
        Storage::fake('local');

        $this->actingAs($this->user)->post(route('files.store', $this->idea), [
            'files' => [UploadedFile::fake()->create('../../evil name.pdf', 10)],
        ]);

        $file = IdeaFile::first();

        // Symfony strips any directory component from the client-supplied name,
        // so no traversal segment survives even in the display name...
        $this->assertSame('evil name.pdf', $file->original_name);

        // ...and the path actually written to disk is generated independently
        // of whatever the client called the file.
        $this->assertStringNotContainsString('..', $file->path);
        $this->assertStringNotContainsString(' ', $file->path);
        $this->assertStringNotContainsString('evil', $file->path);
    }

    public function test_disallowed_and_oversized_files_are_rejected(): void
    {
        Storage::fake('local');

        $this->actingAs($this->user)
            ->post(route('files.store', $this->idea), [
                'files' => [UploadedFile::fake()->create('shell.php', 10)],
            ])
            ->assertSessionHasErrors('files.0');

        $this->actingAs($this->user)
            ->post(route('files.store', $this->idea), [
                // 11 MB, over the 10 MB cap.
                'files' => [UploadedFile::fake()->create('big.pdf', 11264)],
            ])
            ->assertSessionHasErrors('files.0');

        $this->assertDatabaseCount('idea_files', 0);
    }

    public function test_an_owner_can_download_their_file_as_an_attachment(): void
    {
        Storage::fake('local');

        $path = UploadedFile::fake()->create('notes.pdf', 20)->store('idea-files/x', 'local');
        $file = IdeaFile::factory()->for($this->idea)->create([
            'path' => $path,
            'original_name' => 'notes.pdf',
        ]);

        $response = $this->actingAs($this->user)->get(route('files.show', $file));

        $response->assertOk();
        // Never rendered inline, so an uploaded document cannot execute here.
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
    }

    public function test_deleting_a_file_removes_it_from_storage(): void
    {
        Storage::fake('local');

        $path = UploadedFile::fake()->create('temp.pdf', 20)->store('idea-files/x', 'local');
        $file = IdeaFile::factory()->for($this->idea)->create(['path' => $path]);

        Storage::disk('local')->assertExists($path);

        $this->actingAs($this->user)
            ->delete(route('files.destroy', $file))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('idea_files', 0);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_a_missing_file_returns_a_not_found_rather_than_an_error(): void
    {
        Storage::fake('local');

        $file = IdeaFile::factory()->for($this->idea)->create(['path' => 'idea-files/gone.pdf']);

        $this->actingAs($this->user)
            ->get(route('files.show', $file))
            ->assertNotFound();
    }

    /*
    |--------------------------------------------------------------------------
    | Links
    |--------------------------------------------------------------------------
    */

    public function test_a_user_can_add_a_link_to_their_idea(): void
    {
        $this->actingAs($this->user)
            ->post(route('links.store', $this->idea), [
                'label' => 'Reference build',
                'url' => 'https://example.com/build',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('idea_links', [
            'idea_id' => $this->idea->id,
            'label' => 'Reference build',
            'url' => 'https://example.com/build',
        ]);
    }

    public function test_a_bare_host_is_normalised_to_an_https_url(): void
    {
        $this->actingAs($this->user)->post(route('links.store', $this->idea), [
            'label' => 'Docs',
            'url' => 'example.com/docs',
        ]);

        $this->assertDatabaseHas('idea_links', ['url' => 'https://example.com/docs']);
    }

    public function test_non_http_schemes_are_rejected(): void
    {
        foreach (['javascript:alert(1)', 'data:text/html,<script>alert(1)</script>'] as $url) {
            $this->actingAs($this->user)
                ->post(route('links.store', $this->idea), ['label' => 'Bad', 'url' => $url])
                ->assertSessionHasErrors('url');
        }

        $this->assertDatabaseCount('idea_links', 0);
    }

    public function test_a_user_can_edit_and_delete_their_own_link(): void
    {
        $link = IdeaLink::factory()->for($this->idea)->create();

        $this->actingAs($this->user)
            ->patch(route('links.update', $link), [
                'label' => 'Updated label',
                'url' => 'https://updated.example.com',
            ])
            ->assertSessionHas('success');

        $this->assertSame('Updated label', $link->fresh()->label);

        $this->actingAs($this->user)
            ->delete(route('links.destroy', $link))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('idea_links', 0);
    }
}
