<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PollManagementController;
use App\Models\Poll;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');

    Route::get('/auth/oidc/redirect', [AuthController::class, 'oidcRedirect'])->name('auth.oidc.redirect');
    Route::get('/auth/oidc/callback', [AuthController::class, 'oidcCallback'])->name('auth.oidc.callback');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::get('/poll/create', function () {
    return view('polls.create');
})->name('poll.create');

Route::get('/poll/{permalink_token}/published', function (string $permalink_token) {
    $poll = Poll::where('permalink_token', $permalink_token)->firstOrFail();

    return view('polls.published', [
        'poll' => $poll,
    ]);
})->name('poll.published');

Route::get('/poll/{permalink_token}', function (string $permalink_token) {
    return view('polls.vote', [
        'permalink_token' => $permalink_token,
    ]);
})->name('poll.view');

Route::get('/poll/{permalink_token}/vote/{magic_token}', function (string $permalink_token, string $magic_token) {
    return view('polls.vote', [
        'permalink_token' => $permalink_token,
        'magic_token' => $magic_token,
    ]);
})->name('poll.vote.magic');

Route::get('/poll/{permalink_token}/results', function (string $permalink_token) {
    return view('polls.results', [
        'permalink_token' => $permalink_token,
    ]);
})->name('poll.results');

Route::get('/poll/{permalink_token}/manage', [PollManagementController::class, 'show'])->name('poll.manage');
Route::patch('/poll/{permalink_token}/manage', [PollManagementController::class, 'update'])->name('poll.manage.update');
Route::delete('/poll/{permalink_token}/manage', [PollManagementController::class, 'destroy'])->name('poll.manage.delete');

Route::get('/dashboard', DashboardController::class)->middleware('auth')->name('dashboard');

Route::get('/healthz', function () {
    try {
        DB::connection()->getPdo();
        Redis::connection()->ping();

        return response()->json(['status' => 'ok']);
    } catch (\Throwable $exception) {
        return response()->json([
            'status' => 'error',
            'detail' => $exception->getMessage(),
        ], 503);
    }
})->name('healthz');
