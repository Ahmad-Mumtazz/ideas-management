<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\SessionsController;
use App\Http\Controllers\CheckpointController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IdeaController;
use App\Http\Controllers\IdeaFileController;
use App\Http\Controllers\IdeaLinkController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : view('welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Guest only
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/login', [SessionsController::class, 'create'])->name('login');
    // Throttled in LoginRequest per email+IP; this is a coarse per-IP backstop.
    Route::post('/login', [SessionsController::class, 'store'])->middleware('throttle:20,1');
});

/*
|--------------------------------------------------------------------------
| Authenticated
|--------------------------------------------------------------------------
|
| Every route below is behind `auth`. Ownership on top of that is enforced by
| the policies (IdeaPolicy, CheckpointPolicy, IdeaLinkPolicy, IdeaFilePolicy),
| not by which links happen to be rendered in the UI.
|
*/

Route::middleware('auth')->group(function () {
    Route::delete('/logout', [SessionsController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Idea-scoped extras, declared before the resource for clarity.
    Route::get('/ideas/{idea}/cover', [IdeaController::class, 'cover'])->name('ideas.cover');
    Route::patch('/ideas/{idea}/archive', [IdeaController::class, 'archive'])->name('ideas.archive');
    Route::patch('/ideas/{idea}/restore', [IdeaController::class, 'restore'])->name('ideas.restore');

    Route::resource('ideas', IdeaController::class);

    // Children use shallow nesting: creation hangs off the parent idea, while
    // updates and deletes address the child directly and are authorized
    // through the child's policy, which defers to the parent idea's owner.
    Route::post('/ideas/{idea}/checkpoints', [CheckpointController::class, 'store'])->name('checkpoints.store');
    Route::patch('/checkpoints/{checkpoint}', [CheckpointController::class, 'update'])->name('checkpoints.update');
    Route::patch('/checkpoints/{checkpoint}/toggle', [CheckpointController::class, 'toggle'])->name('checkpoints.toggle');
    Route::delete('/checkpoints/{checkpoint}', [CheckpointController::class, 'destroy'])->name('checkpoints.destroy');

    Route::post('/ideas/{idea}/links', [IdeaLinkController::class, 'store'])->name('links.store');
    Route::patch('/links/{link}', [IdeaLinkController::class, 'update'])->name('links.update');
    Route::delete('/links/{link}', [IdeaLinkController::class, 'destroy'])->name('links.destroy');

    Route::post('/ideas/{idea}/files', [IdeaFileController::class, 'store'])->name('files.store');
    Route::get('/files/{file}', [IdeaFileController::class, 'show'])->name('files.show');
    Route::delete('/files/{file}', [IdeaFileController::class, 'destroy'])->name('files.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/photo', [ProfileController::class, 'photo'])->name('profile.photo');
});
