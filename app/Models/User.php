<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

/**
 * `email` stays fillable so registration can set it once, but the profile
 * update path only ever fills validated `name`/`phone`, so it cannot be changed
 * afterwards — see ProfileController and ProfileUpdateRequest.
 */
#[Fillable(['name', 'email', 'phone', 'password', 'profile_photo'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function ideas(): HasMany
    {
        return $this->hasMany(Idea::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function deleteProfilePhoto(): void
    {
        if ($this->profile_photo) {
            Storage::disk('local')->delete($this->profile_photo);
        }
    }

    /**
     * The profile photo URL, or null when none is set.
     *
     * The `v` parameter is derived from the stored path, which is regenerated
     * on every upload. Without it the URL never changes, so a browser holding a
     * cached copy keeps showing the *previous* picture after a replacement.
     */
    public function profilePhotoUrl(): ?string
    {
        if (! $this->profile_photo) {
            return null;
        }

        return route('profile.photo', [
            'v' => substr(md5($this->profile_photo), 0, 8),
        ]);
    }

    /**
     * Up to two letters, used for the fallback avatar when no photo is set.
     */
    public function getInitialsAttribute(): string
    {
        return Str::of($this->name)
            ->trim()
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');
    }
}
