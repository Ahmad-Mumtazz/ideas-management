<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        // The `password` cast on User hashes this on save.
        $user = User::create($request->validated());

        event(new Registered($user));

        Auth::login($user);

        // SessionGuard::login() migrates the session, but the token is rotated
        // explicitly here as well so nothing survives from the guest session.
        $request->session()->regenerate();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Welcome, '.$user->name.'! Your account is ready.');
    }
}
