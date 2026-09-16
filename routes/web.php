<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FurnitureCategoryController;
use App\Http\Controllers\FurnitureController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/catalog', [FurnitureCategoryController::class, 'index'])->name('catalog');
Route::get('/catalog/categories/{category_id}', [FurnitureController::class, 'index'])
    ->whereNumber('category_id')
    ->name('catalog.category');
Route::get('/catalog/furniture/{furniture_id}', [FurnitureController::class, 'show'])
    ->whereNumber('furniture_id')
    ->name('catalog.furniture.show');

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');
        Route::get('categories', [FurnitureCategoryController::class, 'index'])->name('categories.index');
        Route::get('categories/{category_id}', [FurnitureController::class, 'index'])
            ->whereNumber('category_id')
            ->name('categories.show');
        Route::get('furniture/{furniture_id}', [FurnitureController::class, 'show'])
            ->whereNumber('furniture_id')
            ->name('furniture.show');
    });

Route::middleware(['auth'])->group(function () {
    Route::post('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
    Route::delete('invitations/{invitation}', [TeamInvitationController::class, 'decline'])->name('invitations.decline');
});

Route::middleware(['auth'])->prefix('cart')->name('cart.')->group(function () {
    Route::post('add', [CartController::class, 'add'])->name('add');
    Route::post('items/{furniture_id}/increment', [CartController::class, 'increment'])
        ->whereNumber('furniture_id')
        ->name('increment');
    Route::post('items/{furniture_id}/decrement', [CartController::class, 'decrement'])
        ->whereNumber('furniture_id')
        ->name('decrement');
    Route::delete('items/{furniture_id}', [CartController::class, 'remove'])
        ->whereNumber('furniture_id')
        ->name('remove');
    Route::post('clear', [CartController::class, 'clear'])->name('clear');
});

require __DIR__.'/settings.php';
