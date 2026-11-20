<?php

use App\Http\Controllers\WallController;
use Illuminate\Support\Facades\Route;

// Read by the embed widget from other sites. CORS for api/* comes from Laravel's default config.
Route::get('/walls/{project}', [WallController::class, 'json'])
    ->middleware('cache.headers:public;max_age=60;etag')
    ->name('api.wall');
