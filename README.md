# Laravel Simple Repository

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![CI Status](https://github.com/elegantmedia/Laravel-Simple-Repository/actions/workflows/ci.yml/badge.svg)](https://github.com/elegantmedia/Laravel-Simple-Repository/actions/workflows/ci.yml)
[![Code Coverage](https://codecov.io/gh/elegantmedia/Laravel-Simple-Repository/branch/main/graph/badge.svg)](https://codecov.io/gh/elegantmedia/Laravel-Simple-Repository)

A clean and type-safe implementation of the repository pattern for Laravel 12+ applications.

## Version Compatibility

| Package Version | Laravel Version | PHP Version | Branch |
|-----------------|-----------------|-------------|--------|
| 5.x             | 12.x            | ^8.2        | v5.x   |
| 4.x             | 11.x            | ^8.2        | v4.x   |
| 3.x             | 10.x            | ^8.1        | master |
| 2.x             | 9.x             | ^8.0        | master |

## Documentation

- **[Architecture Guide](docs/ARCHITECTURE.md)** - Understand the design decisions, patterns, and component relationships
- **[Development Guide](docs/DEVELOPMENT.md)** - Contributing guidelines, testing strategies, and development workflow
- **[Migration Guide](docs/MIGRATION_GUIDE_v4-v5.md)** - Upgrade from v4.x to v5.x with breaking changes and new features
- **[Examples](examples/EXAMPLES.md)** - Complete working examples showing real-world usage patterns
- **[Changelog](CHANGELOG.md)** - Version history and release notes

## What is Laravel Simple Repository?

Laravel Simple Repository provides a powerful abstraction layer between your application logic and data persistence. It implements the repository pattern with modern PHP features, offering a clean, maintainable, and testable approach to data access.

### Why Use This Package?

- **Separation of Concerns**: Keep your controllers thin and your models focused on business logic by moving data access to dedicated repository classes
- **Type Safety**: Built with PHP 8.2+ features for full type hinting and IDE autocompletion support
- **Testability**: Easy to mock and test your data layer without touching the database
- **Consistency**: Standardized API across all your repositories with common operations pre-built
- **Flexibility**: Advanced filtering system that doesn't duplicate Laravel's query builder
- **Performance**: Built-in support for eager loading, pagination, and query optimization
- **Transaction Safety**: Comprehensive transaction support for data integrity


## Repository Methods

### Complete Method Reference

| Method                                                        | Description                                              |
|---------------------------------------------------------------|----------------------------------------------------------|
| **Query Building**                                            |                                                          |
| `newModel()`                                                  | Create a new instance of the model                       |
| `newQuery()`                                                  | Create a new query builder instance                      |
| `newFilter($defaults = true)`                                 | Create a new filter instance for complex queries         |
|                                                               |                                                          |
| **List/Search Operations**                                    |                                                          |
| `all($with = [])`                                             | Get all models with optional relationships               |
| `paginate($perPage = 50, $with = [], $filter = null)`         | Paginate results with optional relationships and filters |
| `simplePaginate($perPage = 50, $with = [], $filter = null)`   | Simple pagination without total count                    |
| `search($filter = null)`                                      | Search models using the provided filter                  |
| `searchByTerm($term)`                                         | Simple search for models by a search term                |
| `searchPaginated($term, $perPage = 50)`                       | Search models with pagination                            |
|                                                               |                                                          |
| **Find Operations**                                           |                                                          |
| `find($id, $with = [])`                                       | Find a model by its primary key                          |
| `findByUuid($uuid, $with = [])`                               | Find a model by UUID                                     |
| `findByField($field, $value, $with = [])`                     | Find a model by a specific field value                   |
| `findOrCreate($searchAttributes, $additionalAttributes = [])` | Find or create a model with given attributes             |
| `findOrCreateById($id, $attributes, $idColumn = 'id')`        | Find or create a model by ID                             |
| `findOrFail($id)`                                             | Find a model by ID or throw exception                    |
| `findByFieldOrFail($field, $value)`                           | Find by field or throw exception                         |
| `findWithTrashed($id)`                                        | Find including soft deleted records                      |
| `findOnlyTrashed($id)`                                        | Find from soft deleted records only                      |
| `findMany($ids)`                                              | Find multiple models by their primary keys               |
| `findManyByField($field, $value)`                             | Find multiple models by field value                      |
|                                                               |                                                          |
| **Create Operations**                                         |                                                          |
| `create($attributes)`                                         | Create a new model instance                              |
| `createMany($records)`                                        | Create multiple model instances                          |
| `firstOrNew($attributes, $values = [])`                       | Get first matching model or instantiate new              |
| `firstOrCreate($attributes, $values = [])`                    | Get first matching model or create it                    |
|                                                               |                                                          |
| **Update Operations**                                         |                                                          |
| `updateModel($model, $attributes)`                            | Update a model instance                                  |
| `updateById($id, $attributes, $idColumn = 'id')`              | Update a model by ID                                     |
| `updateWhere($where, $data)`                                  | Update models matching conditions                        |
| `updateOrCreateById($id, $attributes, $idColumn = 'id')`      | Update or create a model by ID                           |
| `updateOrCreateByUuid($attributes)`                           | Update or create a model by UUID                         |
| `save($model)`                                                | Save a model instance                                    |
|                                                               |                                                          |
| **Delete Operations**                                         |                                                          |
| `delete($id)`                                                 | Delete a model by ID                                     |
| `deleteWhere($where)`                                         | Delete models matching conditions                        |
| `deleteManyByIds($ids)`                                       | Delete multiple models by IDs                            |
| `restore($id)`                                                | Restore a soft deleted model                             |
| `forceDelete($id)`                                            | Permanently delete a model                               |
|                                                               |                                                          |
| **Aggregate Methods**                                         |                                                          |
| `sum($column, $where = [])`                                   | Get the sum of a column                                  |
| `avg($column, $where = [])`                                   | Get the average of a column                              |
| `min($column, $where = [])`                                   | Get the minimum value of a column                        |
| `max($column, $where = [])`                                   | Get the maximum value of a column                        |
| `count($where = [])`                                          | Count models matching conditions                         |
|                                                               |                                                          |
| **Utility Methods**                                           |                                                          |
| `exists($where = [])`                                         | Check if models exist with conditions                    |
| `value($column, $where = [])`                                 | Get single column value from first result                |
| `pluck($column, $where = [], $key = null)`                    | Get array of column values                               |
| `chunk($count, $callback, $where = [])`                       | Process results in chunks                                |
| `random($count = 1)`                                          | Get random model(s)                                      |
|                                                               |                                                          |
| **Transaction Methods**                                       |                                                          |
| `withTransaction($enabled = true)`                            | Enable/disable automatic transaction wrapping            |
| `transaction($callback)`                                      | Execute callback within a transaction                    |
| `beginTransaction()`                                          | Start a new database transaction                         |
| `commit()`                                                    | Commit the active transaction                            |
| `rollback()`                                                  | Rollback the active transaction                          |
| `transactionLevel()`                                          | Get number of active transactions                        |
|                                                               |                                                          |
| **Model Information**                                         |                                                          |
| `getModelClass()`                                             | Get the fully qualified class name                       |
| `getModel()`                                                  | Get the model instance                                   |

## Filter Methods

The SearchFilter class provides powerful query building capabilities through composition. These methods can be chained to build complex queries.

### Laravel Query Builder Methods

**Important**: The SearchFilter class automatically inherits ALL Laravel query builder methods through composition. This means you can use any Eloquent query builder method like `where()`, `whereIn()`, `whereNull()`, `whereBetween()`, `whereHas()`, `orderBy()`, `with()`, `select()`, `join()`, `groupBy()`, and many more without us having to duplicate them in our codebase.

```php
// All Laravel query builder methods are available
$filter = $repository->newFilter()
    ->where('status', 'active')
    ->whereIn('role', ['admin', 'moderator'])
    ->whereHas('posts', function ($query) {
        $query->where('published', true);
    })
    ->with(['profile', 'posts'])
    ->orderBy('created_at', 'desc')
    ->select('id', 'name', 'email');
```

### Repository-Specific Filter Methods

These are custom methods provided by the repository pattern that enhance the filtering experience:

| Method                          | Description                              |
|---------------------------------|------------------------------------------|
| `setKeyword($keyword)`          | Set search keyword for searchable models |
| `setSortBy($field)`             | Set the default sort field               |
| `setSortDirection($direction)`  | Set sort direction ('asc' or 'desc')     |
| `setPaginate($paginate = true)` | Enable/disable pagination                |
| `setPerPage($perPage)`          | Set items per page (max 100)             |

### Date Filtering Methods

These methods are available through the `DateFilterableTrait` and provide convenient date-based filtering:

| Method                                                   | Description                                         |
|----------------------------------------------------------|-----------------------------------------------------|
| `whereDateIs($date, $column = 'created_at')`             | Filter where date equals the given date             |
| `whereDateTimeIs($dateTime, $column = 'created_at')`     | Filter where datetime equals the given datetime     |
| `whereDateBefore($date, $column = 'created_at')`         | Filter where date is before the given date          |
| `whereDateAfter($date, $column = 'created_at')`          | Filter where date is after the given date           |
| `whereDateBetween($start, $end, $column = 'created_at')` | Filter where date is between two dates (inclusive)  |
| `whereDateInPeriod($period, $column = 'created_at')`     | Filter where date is within a Carbon period         |
| `whereDateWithin($interval, $column = 'created_at')`     | Filter within interval from now (e.g., last 7 days) |
| `whereDateToday($column = 'created_at')`                 | Filter records created today                        |
| `whereDateYesterday($column = 'created_at')`             | Filter records created yesterday                    |
| `whereDateThisWeek($column = 'created_at')`              | Filter records created this week                    |
| `whereDateLastWeek($column = 'created_at')`              | Filter records created last week                    |
| `whereDateThisMonth($column = 'created_at')`             | Filter records created this month                   |
| `whereDateLastMonth($column = 'created_at')`             | Filter records created last month                   |
| `whereDateThisYear($column = 'created_at')`              | Filter records created this year                    |
| `whereDateLastYear($column = 'created_at')`              | Filter records created last year                    |
| `whereDateLastDays($days, $column = 'created_at')`       | Filter records created in the last N days           |
| `whereDateLastHours($hours, $column = 'created_at')`     | Filter records created in the last N hours          |
| `whereDateLastMinutes($minutes, $column = 'created_at')` | Filter records created in the last N minutes        |

### Financial Date Filtering Methods

These methods are available through the `FinancialDateFilterableTrait` and provide financial period filtering:

| Method                                                               | Description                                        |
|----------------------------------------------------------------------|----------------------------------------------------|
| `whereDateThisQuarter($column = 'created_at')`                       | Filter records created this quarter                |
| `whereDateLastQuarter($column = 'created_at')`                       | Filter records created last quarter                |
| `whereDateInQuarter($quarter, $year = null, $column = 'created_at')` | Filter records in a specific quarter (1-4)         |
| `whereDateInThisFinancialYear($column = 'created_at')`               | Filter records in current financial year (Jul-Jun) |
| `whereDateInLastFinancialYear($column = 'created_at')`               | Filter records in last financial year              |
| `whereDateInFinancialYear($endingYear, $column = 'created_at')`      | Filter records in specific financial year          |

### Usage Examples

```php
// Create a filter with date conditions
$filter = $repository->newFilter()
    ->whereDateThisMonth()
    ->whereDateInThisFinancialYear('revenue_date')
    ->where('status', 'active')
    ->orderBy('created_at', 'desc');

// Apply the filter
$results = $repository->search($filter);
```

## Detailed Usage Examples

### Query Building Methods

```php
// newModel() - Create a new model instance without saving
$user = $repository->newModel();
$user->name = 'John Doe';
$user->email = 'john@example.com';
$repository->save($user);

// newQuery() - Get a query builder for complex operations
$query = $repository->newQuery()
    ->where('active', true)
    ->whereYear('created_at', 2024);
$count = $query->count();

// newFilter() - Create a reusable filter
$activeUsersFilter = $repository->newFilter()
    ->where('status', 'active')
    ->where('verified', true);
```

### When to Use newFilter() vs newQuery()

#### Use `newFilter()` for:
- **Search and filtering operations** - When you need to build complex search queries
- **Reusable query configurations** - When you want to define a filter once and use it multiple times
- **Dynamic queries** - When building queries based on user input or request parameters
- **Paginated results** - When you need built-in pagination control
- **Repository pattern adherence** - When you want to maintain clean separation of concerns

```php
// Example: Building a reusable filter for search operations
$filter = $repository->newFilter()
    ->setKeyword('Laravel')
    ->where('status', 'active')
    ->whereIn('category', ['tutorial', 'guide'])
    ->whereDateThisMonth()
    ->setSortBy('views')
    ->setSortDirection('desc')
    ->setPaginate(true)
    ->setPerPage(20);

// Use the filter with repository methods
$results = $repository->search($filter);  // Returns paginated results
$allResults = $repository->paginate(50, [], $filter);  // Custom pagination
```

#### Use `newQuery()` for:
- **Direct query operations** - When you need raw access to Laravel's query builder
- **Custom aggregations** - When the repository's aggregate methods aren't sufficient
- **Complex joins or subqueries** - When you need advanced SQL operations
- **One-off queries** - When you don't need the filter's features
- **Performance-critical operations** - When you need maximum control over the query

```php
// Example: Direct query builder for custom operations
$query = $repository->newQuery()
    ->select('department', DB::raw('COUNT(*) as total'))
    ->where('active', true)
    ->groupBy('department')
    ->having('total', '>', 10);

$departments = $query->get();

// Example: Complex join operation
$results = $repository->newQuery()
    ->join('posts', 'users.id', '=', 'posts.user_id')
    ->where('posts.published', true)
    ->whereYear('posts.created_at', 2024)
    ->select('users.*', DB::raw('COUNT(posts.id) as post_count'))
    ->groupBy('users.id')
    ->orderBy('post_count', 'desc')
    ->limit(10)
    ->get();
```

#### Key Differences:

| Feature             | newFilter()                                  | newQuery()                                |
|---------------------|----------------------------------------------|-------------------------------------------|
| **Purpose**         | Search and filtering with repository pattern | Direct Eloquent query builder access      |
| **Return Type**     | FilterableInterface                          | Eloquent\Builder                          |
| **Pagination**      | Built-in pagination control                  | Manual pagination required                |
| **Reusability**     | Designed for reuse                           | One-time use                              |
| **Search Features** | `setKeyword()` for model's searchable fields | Manual search implementation              |
| **Method Chaining** | Repository-specific + Laravel methods        | Only Laravel query builder methods        |
| **Best For**        | Application search features, API filters     | Complex SQL, performance-critical queries |

### Pagination Methods

```php
// paginate() - Standard Laravel pagination with total count
$users = $repository->paginate(20); // 20 items per page
$users = $repository->paginate(20, ['profile', 'posts']); // With eager loading
$users = $repository->paginate(20, [], $filter); // With custom filter

// simplePaginate() - More efficient pagination without total count
$users = $repository->simplePaginate(50); // Faster for large datasets
$users = $repository->simplePaginate(50, ['profile']); // With relations
```

### Search Methods

```php
// search() - Flexible search that respects filter's pagination setting
$filter = $repository->newFilter()
    ->setKeyword('john')
    ->setPaginate(true)
    ->setPerPage(20);
$results = $repository->search($filter); // Returns LengthAwarePaginator

$filter->setPaginate(false);
$results = $repository->search($filter); // Returns Collection

// searchByTerm() - Simple search returning all results
$users = $repository->searchByTerm('john@example.com'); // Returns Collection
// Good for autocomplete, dropdowns, or small result sets

// searchPaginated() - Always returns paginated results
$results = $repository->searchPaginated('john', 25); // 25 per page
$results = $repository->searchPaginated('admin', 10); // 10 per page
// Perfect for search results pages, data tables
```

### Update Methods

```php
// updateModel() - Update an existing model instance
$user = $repository->find(1);
$updated = $repository->updateModel($user, [
    'name' => 'Jane Doe',
    'email' => 'jane@example.com'
]);

// updateById() - Update without fetching the model first
$updated = $repository->updateById(1, [
    'last_login' => now(),
    'login_count' => DB::raw('login_count + 1')
]);

// updateWhere() - Bulk update with conditions
$affectedRows = $repository->updateWhere(
    ['status' => 'pending', 'created_at' => '<', now()->subDays(7)],
    ['status' => 'expired']
);
```

### Aggregate Methods

```php
// sum() - Calculate sum of a column
$totalRevenue = $repository->sum('revenue');
$monthlyRevenue = $repository->sum('revenue', [
    'created_at' => '>=', now()->startOfMonth()
]);

// avg() - Calculate average
$averagePrice = $repository->avg('price');
$averageRating = $repository->avg('rating', ['status' => 'published']);

// min() - Get minimum value
$lowestPrice = $repository->min('price');
$earliestDate = $repository->min('created_at', ['status' => 'active']);
```

### Utility Methods

```php
// exists() - Check if records exist
if ($repository->exists(['email' => 'john@example.com'])) {
    // Email already taken
}

// value() - Get a single column value
$userName = $repository->value('name', ['id' => 1]);
$latestLogin = $repository->value('last_login', ['email' => 'john@example.com']);

// pluck() - Get array of values
$names = $repository->pluck('name'); // Collection of all names
$emailsByName = $repository->pluck('email', [], 'name'); // Keyed by name
$activeEmails = $repository->pluck('email', ['status' => 'active']);

// chunk() - Process large datasets efficiently
$repository->chunk(1000, function ($users) {
    foreach ($users as $user) {
        // Process each user
        Mail::to($user)->send(new Newsletter());
    }
}, ['subscribed' => true]);

// random() - Get random records
$randomUser = $repository->random(); // Single random model
$randomUsers = $repository->random(5); // Collection of 5 random models
```

### Transaction Methods

```php
// withTransaction() - Enable automatic transactions
$repository->withTransaction(); // Enable
$user = $repository->create(['name' => 'John']); // Wrapped in transaction
$repository->updateModel($user, ['verified' => true]); // Also wrapped
$repository->withTransaction(false); // Disable

// transaction() - Callback-based transactions
$result = $repository->transaction(function ($repo) {
    $user = $repo->create(['name' => 'Jane']);
    $profile = $repo->create(['user_id' => $user->id]);
    
    if (!$user->isValid()) {
        throw new \Exception('Invalid user');
    }
    
    return $user;
}); // Automatically rolled back on exception
```

### Filter-Specific Methods

```php
// setKeyword() - Search in model's searchable fields
$filter = $repository->newFilter()
    ->setKeyword('john doe'); // Searches in fields defined by model

// setSortBy() and setSortDirection()
$filter->setSortBy('created_at')
    ->setSortDirection('desc'); // Latest first

// setPaginate() - Control pagination behavior
$filter->setPaginate(true); // Enable pagination
$filter->setPaginate(false); // Disable - returns all results

// setPerPage() - Control page size
$filter->setPerPage(100); // Max 100 items per page
$filter->setPerPage(10); // 10 items per page
```

### Date Filtering Examples

```php
use Carbon\Carbon;
use Carbon\CarbonPeriod;

// whereDateIs() - Exact date match
$filter = $repository->newFilter()
    ->whereDateIs(Carbon::parse('2024-01-15')); // All records from Jan 15, 2024

// whereDateInPeriod() - Using Carbon periods
$period = CarbonPeriod::create('2024-01-01', '2024-01-31');
$filter = $repository->newFilter()
    ->whereDateInPeriod($period); // All January 2024 records

// Custom column filtering
$filter = $repository->newFilter()
    ->whereDateIs(Carbon::today(), 'published_at')
    ->whereDateInPeriod($period, 'approved_at');
```

## Quick Start

### Installation

```bash
composer require elegantmedia/laravel-simple-repository
```

### Configuration

Publish the configuration file to customize default settings:

```bash
php artisan vendor:publish --provider="ElegantMedia\SimpleRepository\SimpleRepositoryServiceProvider" --tag="simple-repository-config"
```

This will create `config/simple-repository.php` where you can configure:

- **Default Pagination**: Set default `per_page` and `max_per_page` values
- **Default Sorting**: Configure default sort field and direction
- **Search Settings**: Customize search query parameter and case sensitivity
- **Repository Command**: Set default directory and repository suffix for generated files

Example configuration:

```php
return [
    'defaults' => [
        'pagination' => [
            'per_page' => 50,
            'max_per_page' => 100,
        ],
        'sorting' => [
            'field' => 'created_at',
            'direction' => 'desc',
        ],
    ],
    'search' => [
        'query_parameter' => 'q',
        'case_sensitive' => false,
    ],
    'command' => [
        'directory' => 'Models',
        'suffix' => 'Repository',
    ],
];
```

### Basic Usage

#### 1. Create a repository:

```bash
php artisan make:repository User
```

#### 2. Use in your controller:

```php
<?php

namespace App\Http\Controllers;

use App\Models\UsersRepository;

class UserController extends Controller
{
    public function __construct(
        private UsersRepository $users
    ) {}

    public function index()
    {
        return $this->users->paginate(20);
    }

    public function show(int $id)
    {
        return $this->users->findOrFail($id);
    }
}
```

#### 3. Add search to your model:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use ElegantMedia\SimpleRepository\Search\Traits\SearchableLike;

class User extends Model
{
    use SearchableLike;

    protected array $searchable = [
        'name',
        'email',
    ];
}
```

#### 4. Search with filters:

```php
$filter = $repository->newFilter()
    ->where('status', 'active')
    ->with(['posts', 'comments'])
    ->setSortBy('created_at');

$users = $repository->search($filter);
```

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/elegantmedia/laravel-simple-repository.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/elegantmedia/laravel-simple-repository
