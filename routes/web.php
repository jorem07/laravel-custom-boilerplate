<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::view('/test', 'test');
 
// Fallback route for auth if request hits root without /api prefix
Route::prefix('auth')->middleware(['log.route'])->group(function () {
    Route::post('login', [\App\Http\Controllers\AuthController::class, 'login']);
});
