<?php

use Illuminate\Support\Facades\Route;

// API Routes
Route::prefix('api')->group(function () {
    // API endpoints will be added in future phases
});

// Catch-all route for SPA
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
