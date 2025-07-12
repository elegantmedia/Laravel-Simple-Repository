<?php

use Examples\App\Http\Controllers\CarsAdminController;
use Examples\App\Http\Controllers\CarsController;
use Illuminate\Support\Facades\Route;

/*
 * Public Car Routes
 *
 * These routes are for the public-facing car listings
 * with 20 items per page pagination
 */

// Public car browsing routes
Route::prefix('cars')->name('cars.')->group(function () {
	// Browse all available cars
	Route::get('/', [CarsController::class, 'index'])->name('index');

	// View specific car details
	Route::get('/{id}', [CarsController::class, 'show'])->name('show');

	// Browse cars by make
	Route::get('/make/{make}', [CarsController::class, 'byMake'])->name('by-make');

	// Submit inquiry about a car
	Route::post('/{id}/inquiry', [CarsController::class, 'inquiry'])->name('inquiry');
});

// Featured cars component (for homepage)
Route::get('/featured-cars', [CarsController::class, 'featured'])->name('featured-cars');

/*
 * Admin Car Routes
 *
 * These routes are for the admin dashboard
 * with 50 items per page pagination and full CRUD operations
 */

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
	// Car management routes
	Route::prefix('cars')->name('cars.')->group(function () {
		// List all cars with advanced filtering
		Route::get('/', [CarsAdminController::class, 'index'])->name('index');

		// Show create form
		Route::get('/create', [CarsAdminController::class, 'create'])->name('create');

		// Store new car
		Route::post('/', [CarsAdminController::class, 'store'])->name('store');

		// View car details (including soft-deleted)
		Route::get('/{id}', [CarsAdminController::class, 'show'])->name('show');

		// Show edit form
		Route::get('/{id}/edit', [CarsAdminController::class, 'edit'])->name('edit');

		// Update car
		Route::put('/{id}', [CarsAdminController::class, 'update'])->name('update');

		// Soft delete car
		Route::delete('/{id}', [CarsAdminController::class, 'destroy'])->name('destroy');

		// Restore soft-deleted car
		Route::post('/{id}/restore', [CarsAdminController::class, 'restore'])->name('restore');

		// Permanently delete car
		Route::delete('/{id}/force', [CarsAdminController::class, 'forceDelete'])->name('force-delete');

		// Bulk actions
		Route::post('/bulk-action', [CarsAdminController::class, 'bulkAction'])->name('bulk-action');

		// Export cars data
		Route::get('/export', [CarsAdminController::class, 'export'])->name('export');
	});
});
