# Laravel Simple Repository - Examples

This directory contains comprehensive examples demonstrating how to use the Laravel Simple Repository package in different scenarios.

## Overview

The examples showcase a car dealership application with three different use cases:

1. **Public Website** (`CarsController`) - Customer-facing car listings with 20 items per page
2. **Admin Dashboard** (`CarsAdminController`) - Administrative interface with 50 items per page
3. **RESTful API** (`CarsAPIController`) - API endpoints with 100 items per page and advanced filtering

## Directory Structure

```
examples/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── CarsController.php          # Public website controller
│   │   │   ├── CarsAdminController.php     # Admin dashboard controller
│   │   │   └── CarsAPIController.php       # API controller
│   │   ├── Requests/
│   │   │   ├── StoreCarRequest.php         # Validation for creating cars
│   │   │   └── UpdateCarRequest.php        # Validation for updating cars
│   │   └── Resources/
│   │       └── CarResource.php             # API resource transformer
│   └── Models/
│       ├── Car.php                         # Car model with searchable trait
│       └── CarsRepository.php              # Repository with custom methods
├── database/
│   ├── migrations/
│   │   └── 2024_01_01_000000_create_cars_table.php
│   └── factories/
│       └── CarFactory.php                  # Factory for testing/seeding
├── routes/
│   ├── web.php                            # Web routes for public and admin
│   └── api.php                            # API routes with examples
└── README.md                              # This file
```

## Key Features Demonstrated

### 1. Repository Pattern Implementation

The `CarsRepository` extends `BaseRepository` and demonstrates:
- Custom query methods (`findAvailable`, `findByPriceRange`)
- Aggregation methods (`getStatusCounts`, `getPopularMakes`)
- Business logic methods (`markAsSold`, `reserve`)
- Advanced filtering with `getForAdminDashboard`

### 2. Different Pagination Strategies

- **Public Website**: 20 items per page for comfortable browsing
- **Admin Dashboard**: 50 items per page for efficient management
- **API**: 100 items per page (configurable) for data transfer

### 3. Search and Filtering

#### Basic Search (Public Website)
```php
$filter = $repository->newFilter()
    ->where('status', 'available')
    ->setKeyword($request->get('search'))
    ->setPerPage(20);
```

#### Advanced Filtering (Admin Dashboard)
```php
$filter = $repository->newFilter();

if (!empty($filters['year_from'])) {
    $filter->where('year', '>=', $filters['year_from']);
}
if (!empty($filters['price_max'])) {
    $filter->where('price', '<=', $filters['price_max']);
}

$filter->setSortBy($filters['sort_by'] ?? 'created_at')
    ->setSortDirection($filters['sort_direction'] ?? 'desc')
    ->setPerPage(50);
```

#### API Filtering via URL Parameters
```
GET /api/v1/cars?search=Toyota&status=available&year_min=2020&price_max=50000&sort=price&order=asc&per_page=100
```

### 4. Transaction Support

Demonstrated in admin controller for data integrity:
```php
$car = $this->carsRepository->transaction(function ($repository) use ($request) {
    $car = $repository->create($request->validated());
    // Additional operations...
    return $car;
});
```

### 5. Soft Deletes

The admin controller shows complete soft delete functionality:
- Soft delete: `$repository->delete($id)`
- Restore: `$repository->restore($id)`
- Force delete: `$repository->forceDelete($id)`
- Find with trashed: `$repository->findWithTrashed($id)`

### 6. Bulk Operations

Admin controller includes bulk actions:
```php
$repository->deleteManyByIds($carIds);
$repository->updateWhere(['id' => $carIds], ['status' => 'sold']);
```

### 7. API Resource Transformation

The `CarResource` demonstrates:
- Conditional attribute inclusion
- Relationship loading
- Custom formatting
- API versioning metadata

## Usage Examples

### Setting Up

1. Copy the examples to your Laravel application
2. Run the migration: `php artisan migrate`
3. Seed test data: `php artisan db:seed --class=CarSeeder`

### Public Website Usage

```php
// Browse available cars
$filter = $repository->newFilter()
    ->where('status', 'available')
    ->setPerPage(20);
$cars = $repository->search($filter);

// Find similar cars
$similarCars = $repository->newFilter()
    ->where('make', $car->make)
    ->where('id', '!=', $car->id)
    ->setPerPage(4)
    ->setPaginate(false);
```

### Admin Dashboard Usage

```php
// Get all cars with filters
$cars = $repository->getForAdminDashboard([
    'search' => 'Toyota',
    'status' => 'available',
    'year_from' => 2020,
    'price_max' => 50000,
    'per_page' => 50
]);

// Bulk update status
$repository->updateWhere(
    ['id' => [1, 2, 3]],
    ['status' => 'sold']
);
```

### API Usage

```bash
# Get cars with filters
curl "https://example.com/api/v1/cars?search=Toyota&status=available&per_page=100"

# Advanced search
curl -X POST "https://example.com/api/v1/cars/search" \
  -H "Content-Type: application/json" \
  -d '{
    "filters": [
      {"field": "make", "operator": "like", "value": "Toy"},
      {"field": "price", "operator": "between", "value": [20000, 50000]}
    ],
    "sort": [
      {"field": "price", "direction": "asc"}
    ]
  }'

# Get statistics
curl "https://example.com/api/v1/cars/stats"
```

## Best Practices Demonstrated

1. **Dependency Injection**: All controllers use constructor injection for the repository
2. **Validation**: Separate request classes for validation logic
3. **Resource Transformation**: API resources for consistent JSON output
4. **Type Declarations**: Strict types and return type declarations throughout
5. **Documentation**: Comprehensive PHPDoc blocks explaining functionality
6. **Security**: Different access levels for public, admin, and API
7. **Performance**: Appropriate indexes in migration for common queries

## Testing

Create a test case using the repository:

```php
use Examples\App\Models\CarsRepository;
use Examples\App\Models\Car;

class CarRepositoryTest extends TestCase
{
    protected CarsRepository $repository;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(CarsRepository::class);
    }
    
    public function test_can_find_available_cars()
    {
        // Arrange
        Car::factory()->available()->count(5)->create();
        Car::factory()->sold()->count(3)->create();
        
        // Act
        $availableCars = $this->repository->findAvailable();
        
        // Assert
        $this->assertCount(5, $availableCars);
        $this->assertTrue($availableCars->every(fn($car) => $car->status === 'available'));
    }
}
```

## Notes

- These examples use Laravel 13 conventions and patterns
- Authentication middleware is referenced but not implemented (use Laravel Sanctum/Passport)
- Activity logging is referenced but not implemented (use spatie/laravel-activitylog)
- The examples assume you have the base Laravel Simple Repository package installed
- Adjust namespaces and paths according to your application structure
