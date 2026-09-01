<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SessionsController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * LoginRequest handles validation, the rate limiter and the credential
     * check, and throws a validation error on failure.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // Guards against session fixation: the pre-login session id is discarded.
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Log out and fully tear down the session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        // Auth::logout() alone leaves the session data and CSRF token in place;
        // both are discarded here so the session cannot be reused.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out.');
    }
}
