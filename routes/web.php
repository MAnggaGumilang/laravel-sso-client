<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth', [AuthController::class, 'index'])->name('auth.index');
Route::get('/sso/callback', [AuthController::class, 'ssoCallback'])->name('auth.sso');

// /home akan diproteksi dengan middleware SSO (dibuat di Langkah D)
Route::get('/home', function () {
    $user = session('user');
    return 'Authenticated as ' . ($user['name'] ?? 'Unknown');
})->name('home')
  ->middleware(\App\Http\Middleware\SsoAuthMiddleware::class);