<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
            'ideaCount' => $request->user()->ideas()->count(),
        ]);
    }

    /**
     * Update the signed-in user's own profile.
     *
     * Only `name` and `phone` are ever written from input. The email address is
     * not in ProfileUpdateRequest::rules(), so even a hand-crafted request
     * containing `email` cannot change it.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->fill([
            'name' => $request->validated('name'),
            'phone' => $request->validated('phone') ?: null,
        ]);

        if ($request->hasFile('profile_photo')) {
            $user->deleteProfilePhoto();
            $user->profile_photo = $request->file('profile_photo')
                ->store('profile-photos/' . $user->id, 'local');
        } elseif ($request->boolean('remove_profile_photo')) {
            $user->deleteProfilePhoto();
            $user->profile_photo = null;
        }

        $user->save();

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Your profile was updated.');
    }


    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        foreach ($user->ideas()->get() as $idea) {
            $idea->delete();
        }

        $user->deleteProfilePhoto();
        $user->delete();

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Your account has been permanently deleted.');
    }


    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = $request->user();

        $user->update([
            'password' => $request->password,
        ]);

        $request->session()->regenerate();

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Your password was changed successfully.');
    }

    /**
     * Serve the signed-in user's own profile photo from the private disk.
     */
    public function photo(Request $request): StreamedResponse
    {
        $user = $request->user();

        abort_if(
            ! $user->profile_photo || ! Storage::disk('local')->exists($user->profile_photo),
            404
        );

        return Storage::disk('local')->response($user->profile_photo, headers: [
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
