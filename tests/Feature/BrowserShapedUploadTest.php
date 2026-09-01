<?php

namespace Tests\Feature;

use App\Models\Idea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The existing image tests send a true PATCH. A browser cannot: it sends a POST
 * with `_method=PATCH`, and it always includes the file input — even when the
 * user picked nothing — as an empty part. These tests reproduce that exact
 * shape so the remove/replace paths are covered the way they are really used.
 */
class BrowserShapedUploadTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * An <input type="file"> the user left alone: PHP reports UPLOAD_ERR_NO_FILE.
     */
    protected function emptyFilePart(): UploadedFile
    {
        return new UploadedFile(
            path: tempnam(sys_get_temp_dir(), 'empty'),
            originalName: '',
            mimeType: null,
            error: UPLOAD_ERR_NO_FILE,
            test: true,
        );
    }

    public function test_removing_a_profile_photo_works_with_an_empty_file_part_present(): void
    {
        Storage::fake('local');

        $path = UploadedFile::fake()->image('me.jpg')->store('profile-photos/1', 'local');
        $this->user->forceFill(['profile_photo' => $path])->save();

        $this->actingAs($this->user)
            ->post(route('profile.update'), [
                '_method' => 'PATCH',
                'name' => $this->user->name,
                'phone' => '',
                'profile_photo' => $this->emptyFilePart(),
                'remove_profile_photo' => '1',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasNoErrors();

        $this->assertNull($this->user->fresh()->profile_photo);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_replacing_a_profile_photo_works_through_a_spoofed_patch(): void
    {
        Storage::fake('local');

        $old = UploadedFile::fake()->image('old.jpg')->store('profile-photos/1', 'local');
        $this->user->forceFill(['profile_photo' => $old])->save();

        $this->actingAs($this->user)
            ->post(route('profile.update'), [
                '_method' => 'PATCH',
                'name' => $this->user->name,
                'profile_photo' => UploadedFile::fake()->image('new.jpg'),
            ])
            ->assertSessionHasNoErrors();

        $new = $this->user->fresh()->profile_photo;

        $this->assertNotNull($new);
        $this->assertNotSame($old, $new);
        Storage::disk('local')->assertMissing($old);
        Storage::disk('local')->assertExists($new);
    }

    public function test_removing_a_cover_image_works_with_an_empty_file_part_present(): void
    {
        Storage::fake('local');

        $path = UploadedFile::fake()->image('cover.jpg')->store('idea-covers/1', 'local');
        $idea = Idea::factory()->for($this->user)->create(['cover_image' => $path]);

        $this->actingAs($this->user)
            ->post(route('ideas.update', $idea), [
                '_method' => 'PATCH',
                'title' => $idea->title,
                'description' => $idea->description,
                'status' => $idea->status->value,
                'priority' => $idea->priority->value,
                'cover_image' => $this->emptyFilePart(),
                'remove_cover_image' => '1',
            ])
            ->assertRedirect(route('ideas.show', $idea))
            ->assertSessionHasNoErrors();

        $this->assertNull($idea->fresh()->cover_image);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_replacing_a_cover_image_works_through_a_spoofed_patch(): void
    {
        Storage::fake('local');

        $old = UploadedFile::fake()->image('old.jpg')->store('idea-covers/1', 'local');
        $idea = Idea::factory()->for($this->user)->create(['cover_image' => $old]);

        $this->actingAs($this->user)
            ->post(route('ideas.update', $idea), [
                '_method' => 'PATCH',
                'title' => $idea->title,
                'description' => $idea->description,
                'status' => $idea->status->value,
                'priority' => $idea->priority->value,
                'cover_image' => UploadedFile::fake()->image('new.jpg'),
            ])
            ->assertSessionHasNoErrors();

        $new = $idea->fresh()->cover_image;

        $this->assertNotNull($new);
        $this->assertNotSame($old, $new);
        Storage::disk('local')->assertMissing($old);
        Storage::disk('local')->assertExists($new);
    }

    /*
    |--------------------------------------------------------------------------
    | Files rejected by PHP before Laravel sees them
    |--------------------------------------------------------------------------
    */

    /**
     * A file larger than `upload_max_filesize`: PHP discards the contents and
     * reports UPLOAD_ERR_INI_SIZE. The user must get a size message, and the
     * picture they already had must survive.
     */
    protected function oversizedPart(): UploadedFile
    {
        return new UploadedFile(
            path: tempnam(sys_get_temp_dir(), 'over'),
            originalName: 'huge.jpg',
            mimeType: 'image/jpeg',
            error: UPLOAD_ERR_INI_SIZE,
            test: true,
        );
    }

    public function test_a_photo_rejected_by_php_reports_the_size_limit_and_keeps_the_existing_one(): void
    {
        Storage::fake('local');

        $existing = UploadedFile::fake()->image('current.jpg')->store('profile-photos/1', 'local');
        $this->user->forceFill(['profile_photo' => $existing])->save();

        $this->from(route('profile.edit'))
            ->actingAs($this->user)
            ->post(route('profile.update'), [
                '_method' => 'PATCH',
                'name' => $this->user->name,
                'profile_photo' => $this->oversizedPart(),
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('profile_photo');

        $this->assertStringContainsString(
            'no larger than',
            session('errors')->first('profile_photo')
        );

        $this->assertSame($existing, $this->user->fresh()->profile_photo);
        Storage::disk('local')->assertExists($existing);
    }

    public function test_a_cover_rejected_by_php_reports_the_size_limit_and_keeps_the_existing_one(): void
    {
        Storage::fake('local');

        $existing = UploadedFile::fake()->image('current.jpg')->store('idea-covers/1', 'local');
        $idea = Idea::factory()->for($this->user)->create(['cover_image' => $existing]);

        $this->actingAs($this->user)
            ->post(route('ideas.update', $idea), [
                '_method' => 'PATCH',
                'title' => $idea->title,
                'description' => $idea->description,
                'status' => $idea->status->value,
                'priority' => $idea->priority->value,
                'cover_image' => $this->oversizedPart(),
            ])
            ->assertSessionHasErrors('cover_image');

        $this->assertStringContainsString(
            'no larger than',
            session('errors')->first('cover_image')
        );

        $this->assertSame($existing, $idea->fresh()->cover_image);
        Storage::disk('local')->assertExists($existing);
    }

    /*
    |--------------------------------------------------------------------------
    | Cache busting
    |--------------------------------------------------------------------------
    */

    /**
     * A replaced image must be served from a different URL, otherwise a cached
     * copy keeps the old picture on screen and the update looks like it failed.
     */
    public function test_image_urls_change_when_the_underlying_file_is_replaced(): void
    {
        Storage::fake('local');

        $idea = Idea::factory()->for($this->user)->create([
            'cover_image' => UploadedFile::fake()->image('one.jpg')->store('idea-covers/1', 'local'),
        ]);

        $before = $idea->coverImageUrl();

        $this->actingAs($this->user)->post(route('ideas.update', $idea), [
            '_method' => 'PATCH',
            'title' => $idea->title,
            'description' => $idea->description,
            'status' => $idea->status->value,
            'priority' => $idea->priority->value,
            'cover_image' => UploadedFile::fake()->image('two.jpg'),
        ]);

        $this->assertNotSame($before, $idea->fresh()->coverImageUrl());

        // And the same for a profile photo.
        $this->user->forceFill([
            'profile_photo' => UploadedFile::fake()->image('a.jpg')->store('profile-photos/1', 'local'),
        ])->save();

        $photoBefore = $this->user->profilePhotoUrl();

        $this->actingAs($this->user)->post(route('profile.update'), [
            '_method' => 'PATCH',
            'name' => $this->user->name,
            'profile_photo' => UploadedFile::fake()->image('b.jpg'),
        ]);

        $this->assertNotSame($photoBefore, $this->user->fresh()->profilePhotoUrl());
    }

    public function test_no_image_means_no_url_so_the_placeholder_is_shown(): void
    {
        $idea = Idea::factory()->for($this->user)->create(['cover_image' => null]);

        $this->assertNull($idea->coverImageUrl());
        $this->assertNull($this->user->profilePhotoUrl());
    }
}
