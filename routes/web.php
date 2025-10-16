<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AdController;
use App\Http\Controllers\AdPageController;

/*Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');*/
Route::get('/', [AdPageController::class, 'index'])->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

// Ad routes (backend JSON endpoints)
Route::middleware('auth')->group(function () {
    Route::post('/ads', [AdController::class, 'store'])->name('ads.store');
    Route::put('/ads/{ad}', [AdController::class, 'update'])->name('ads.update');
    Route::delete('/ads/{ad}', [AdController::class, 'destroy'])->name('ads.destroy');

    // Non-blocked aliases (avoid "ads" for ad blockers)
    Route::post('/listings', [AdController::class, 'store'])->name('listings.store');
    Route::put('/listings/{ad}', [AdController::class, 'update'])->name('listings.update');
    Route::delete('/listings/{ad}', [AdController::class, 'destroy'])->name('listings.destroy');
});

Route::get('/ads', [AdController::class, 'index'])->name('ads.index');
Route::get('/ads/{ad}', [AdController::class, 'show'])->name('ads.show');

// Non-blocked aliases
Route::get('/listings', [AdController::class, 'index'])->name('listings.index');
// Place the create route BEFORE the dynamic parameter route to avoid conflicts
Route::get('/listing/create', [AdPageController::class, 'create'])->middleware('auth')->name('listing.create');
Route::get('/listing/{ad}', [AdController::class, 'show'])->name('listing.show');

// Inertia pages for marketplace
Route::get('/market', [AdPageController::class, 'index'])->name('market.index');
Route::get('/ads/create', [AdPageController::class, 'create'])->middleware('auth')->name('ads.create');
Route::get('/ads/{ad:slug}/view', [AdPageController::class, 'show'])->name('ads.view');

// Non-blocked page aliases
Route::get('/listing/{ad:slug}/view', [AdPageController::class, 'show'])->name('listing.view');
