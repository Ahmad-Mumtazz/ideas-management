<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);
    }

    public function test_a_user_can_update_their_name_and_contact_number(): void
    {
        $this->actingAs($this->user)
            ->patch(route('profile.update'), [
                'name' => 'New Name',
                'phone' => '+44 7700 900123',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('success');

        $this->user->refresh();

        $this->assertSame('New Name', $this->user->name);
        $this->assertSame('+44 7700 900123', $this->user->phone);
    }

    /**
     * The headline guarantee of the profile section.
     */
    public function test_the_email_cannot_be_changed_even_by_a_hand_crafted_request(): void
    {
        $this->actingAs($this->user)
            ->patch(route('profile.update'), [
                'name' => 'New Name',
                'email' => 'attacker@example.com',
            ])
            ->assertRedirect();

        $this->user->refresh();

        $this->assertSame('original@example.com', $this->user->email);
        $this->assertSame('New Name', $this->user->name);
    }

    public function test_profile_validation_rejects_bad_input(): void
    {
        $this->actingAs($this->user)
            ->patch(route('profile.update'), [
                'name' => '',
                'phone' => 'not a phone number!!',
            ])
            ->assertSessionHasErrors(['name', 'phone']);

        $this->assertSame('Original Name', $this->user->fresh()->name);
    }

    public function test_a_user_can_upload_a_profile_picture_and_serve_it_privately(): void
    {
        Storage::fake('local');

        $this->actingAs($this->user)->patch(route('profile.update'), [
            'name' => $this->user->name,
            'profile_photo' => UploadedFile::fake()->image('me.jpg'),
        ]);

        $this->user->refresh();

        $this->assertNotNull($this->user->profile_photo);
        Storage::disk('local')->assertExists($this->user->profile_photo);

        $this->actingAs($this->user)->get(route('profile.photo'))->assertOk();
    }

    public function test_replacing_a_profile_picture_deletes_the_previous_file(): void
    {
        Storage::fake('local');

        $original = UploadedFile::fake()->image('first.jpg')->store('profile-photos/1', 'local');
        $this->user->forceFill(['profile_photo' => $original])->save();

        $this->actingAs($this->user)->patch(route('profile.update'), [
            'name' => $this->user->name,
            'profile_photo' => UploadedFile::fake()->image('second.jpg'),
        ]);

        Storage::disk('local')->assertMissing($original);
        Storage::disk('local')->assertExists($this->user->fresh()->profile_photo);
    }

    public function test_a_user_can_remove_their_profile_picture(): void
    {
        Storage::fake('local');

        $path = UploadedFile::fake()->image('me.jpg')->store('profile-photos/1', 'local');
        $this->user->forceFill(['profile_photo' => $path])->save();

        $this->actingAs($this->user)->patch(route('profile.update'), [
            'name' => $this->user->name,
            'remove_profile_photo' => '1',
        ]);

        $this->assertNull($this->user->fresh()->profile_photo);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_an_invalid_profile_picture_is_rejected(): void
    {
        Storage::fake('local');

        $this->actingAs($this->user)
            ->patch(route('profile.update'), [
                'name' => $this->user->name,
                'profile_photo' => UploadedFile::fake()->create('script.js', 10),
            ])
            ->assertSessionHasErrors('profile_photo');

        $this->actingAs($this->user)
            ->patch(route('profile.update'), [
                'name' => $this->user->name,
                // 3 MB, over the 2 MB cap.
                'profile_photo' => UploadedFile::fake()->image('huge.jpg')->size(3072),
            ])
            ->assertSessionHasErrors('profile_photo');

        $this->assertNull($this->user->fresh()->profile_photo);
    }

    public function test_the_photo_route_returns_not_found_when_no_photo_is_set(): void
    {
        $this->actingAs($this->user)
            ->get(route('profile.photo'))
            ->assertNotFound();
    }
}
