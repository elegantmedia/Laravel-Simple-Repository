<?php

use Examples\App\Http\Controllers\CarsAPIController;
use Illuminate\Support\Facades\Route;

/*
 * API Routes for Cars
 *
 * These routes provide RESTful API endpoints for car data
 * with 100 items per page pagination and comprehensive filtering
 *
 * All routes are prefixed with /api and return JSON responses
 */

// API Version 1
Route::prefix('v1')->name('api.')->group(function () {
	/*
	 * Public API endpoints (no authentication required)
	 */
	Route::prefix('cars')->name('cars.')->group(function () {
		// Get car statistics
		Route::get('/stats', [CarsAPIController::class, 'stats'])->name('stats');

		// Advanced search endpoint (POST)
		Route::post('/search', [CarsAPIController::class, 'search'])->name('search');

		// Standard RESTful endpoints
		Route::get('/', [CarsAPIController::class, 'index'])->name('index');
		Route::get('/{id}', [CarsAPIController::class, 'show'])->name('show');
	});

	/*
	 * Protected API endpoints (authentication required)
	 *
	 * In a real application, you would use Laravel Sanctum or Passport
	 * for API authentication
	 */
	Route::middleware('auth:sanctum')->group(function () {
		Route::prefix('cars')->name('cars.')->group(function () {
			// Create new car
			Route::post('/', [CarsAPIController::class, 'store'])->name('store');

			// Update existing car
			Route::put('/{id}', [CarsAPIController::class, 'update'])->name('update');
			Route::patch('/{id}', [CarsAPIController::class, 'update'])->name('patch');

			// Delete car
			Route::delete('/{id}', [CarsAPIController::class, 'destroy'])->name('destroy');
		});
	});
});

/*
 * API Documentation
 *
 * Example API calls:
 *
 * 1. Basic listing with pagination:
 *    GET /api/v1/cars?page=1&per_page=100
 *
 * 2. Search with filters:
 *    GET /api/v1/cars?search=Toyota&status=available&year_min=2020&price_max=50000
 *
 * 3. Complex search with multiple filters:
 *    GET /api/v1/cars?make=Toyota&model=Camry&color=red&sort=price&order=asc
 *
 * 4. Advanced search (POST):
 *    POST /api/v1/cars/search
 *    {
 *      "filters": [
 *        {"field": "make", "operator": "like", "value": "Toy"},
 *        {"field": "price", "operator": "between", "value": [20000, 50000]},
 *        {"field": "status", "operator": "in", "value": ["available", "reserved"]}
 *      ],
 *      "sort": [
 *        {"field": "price", "direction": "asc"},
 *        {"field": "year", "direction": "desc"}
 *      ],
 *      "pagination": {
 *        "per_page": 50,
 *        "page": 2
 *      }
 *    }
 *
 * 5. Get statistics:
 *    GET /api/v1/cars/stats
 *
 * 6. Create new car (requires authentication):
 *    POST /api/v1/cars
 *    {
 *      "make": "Toyota",
 *      "model": "Camry",
 *      "year": 2024,
 *      "color": "Silver",
 *      "price": 35000,
 *      "vin": "1HGBH41JXMN109186",
 *      "status": "available",
 *      "description": "Brand new 2024 Toyota Camry with all features"
 *    }
 */
