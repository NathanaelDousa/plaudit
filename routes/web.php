<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImportExportController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\WallController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/projects');

Route::get('/setup', [AuthController::class, 'setupForm'])->name('setup');
Route::post('/setup', [AuthController::class, 'setup'])->name('setup.store');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::resource('projects', ProjectController::class);
    Route::resource('projects.testimonials', TestimonialController::class)->only(['create', 'store', 'update', 'destroy'])->scoped(['testimonial' => 'id']);
    Route::post('/projects/{project}/import', [ImportExportController::class, 'import'])->name('projects.import');
    Route::get('/projects/{project}/export.{format}', [ImportExportController::class, 'export'])->whereIn('format', ['csv', 'json'])->name('projects.export');
});

Route::get('/t/{project}', [SubmissionController::class, 'show'])->name('submit');
Route::post('/t/{project}', [SubmissionController::class, 'store'])->middleware('throttle:5,1')->name('submit.store');
Route::get('/t/{project}/thanks', [SubmissionController::class, 'thanks'])->name('submit.thanks');
Route::get('/w/{project}', [WallController::class, 'show'])->name('wall');
