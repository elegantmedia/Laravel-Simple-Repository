# Migration Guide: v4 to v5

## Overview

Version 5.0 introduces a cleaner architecture with better organization and Laravel 12 support. While we've maintained backward compatibility where possible, some changes require updates to your code.

## Breaking Changes

### 1. Namespace Changes

Several classes have been moved to better reflect their purpose:

```php
// Old (v4)
use ElegantMedia\SimpleRepository\SimpleBaseRepository;
use ElegantMedia\SimpleRepository\SimpleRepositoryInterface;

// New (v5)
use ElegantMedia\SimpleRepository\Repository\BaseRepository;
use ElegantMedia\SimpleRepository\Contracts\RepositoryInterface;
```

### 2. Search Classes Reorganization

```php
// Old (v4)
use ElegantMedia\SimpleRepository\Search\Filterable;
use ElegantMedia\SimpleRepository\Search\SearchFilter;
use ElegantMedia\SimpleRepository\Search\Eloquent\SearchableLike;

// New (v5)
use ElegantMedia\SimpleRepository\Search\Contracts\FilterableInterface;
use ElegantMedia\SimpleRepository\Search\Filters\SearchFilter;
use ElegantMedia\SimpleRepository\Search\Traits\SearchableLike;
```

### 3. Base Repository Class Name

```php
// Old (v4)
class UsersRepository extends SimpleBaseRepository
{
    // ...
}

// New (v5)
class UsersRepository extends BaseRepository
{
    // ...
}
```

## Step-by-Step Migration

### Step 1: Update Composer

```bash
composer require elegantmedia/laravel-simple-repository:^5.0
```

### Step 2: Update Repository Classes

Find all repository classes and update the extends clause:

```php
<?php

namespace App\Models\User;

// Remove old import
// use ElegantMedia\SimpleRepository\SimpleBaseRepository;

// Add new import
use ElegantMedia\SimpleRepository\Repository\BaseRepository;

// Update class declaration
class UsersRepository extends BaseRepository
{
    protected string $modelClass = User::class;
    
    // Your custom methods remain the same
}
```

### Step 3: Update Model Traits

Update models using the searchable trait:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// Remove old import
// use ElegantMedia\SimpleRepository\Search\Eloquent\SearchableLike;

// Add new import
use ElegantMedia\SimpleRepository\Search\Traits\SearchableLike;

class User extends Model
{
    use SearchableLike;
    
    // No other changes needed
    protected array $searchable = ['name', 'email'];
}
```

### Step 4: Update Custom Search Filters

If you have custom search filter classes:

```php
<?php

namespace App\Filters;

// Remove old import
// use ElegantMedia\SimpleRepository\Search\SearchFilter;

// Add new import
use ElegantMedia\SimpleRepository\Search\Filters\SearchFilter;

class ActiveUsersFilter extends SearchFilter
{
    // Your implementation remains the same
}
```

### Step 5: Update Interfaces (if used)

If you're using repository interfaces:

```php
<?php

namespace App\Contracts;

// Remove old import
// use ElegantMedia\SimpleRepository\SimpleRepositoryInterface;

// Add new import
use ElegantMedia\SimpleRepository\Contracts\RepositoryInterface;

interface UserRepositoryInterface extends RepositoryInterface
{
    // Your custom method signatures
}
```

### Step 6: Clear Caches

After updating your code:

```bash
php artisan cache:clear
php artisan config:clear
php artisan optimize:clear
composer dump-autoload
```

## Automated Migration Script

For large projects, you can use this script to help with the migration:

```bash
#!/bin/bash

# Find and replace in PHP files
find app -name "*.php" -type f -exec sed -i '' \
  -e 's/SimpleBaseRepository/BaseRepository/g' \
  -e 's/SimpleRepositoryInterface/RepositoryInterface/g' \
  -e 's/use ElegantMedia\\SimpleRepository\\SimpleBaseRepository/use ElegantMedia\\SimpleRepository\\Repository\\BaseRepository/g' \
  -e 's/use ElegantMedia\\SimpleRepository\\SimpleRepositoryInterface/use ElegantMedia\\SimpleRepository\\Contracts\\RepositoryInterface/g' \
  -e 's/use ElegantMedia\\SimpleRepository\\Search\\Eloquent\\SearchableLike/use ElegantMedia\\SimpleRepository\\Search\\Traits\\SearchableLike/g' \
  -e 's/use ElegantMedia\\SimpleRepository\\Search\\SearchFilter/use ElegantMedia\\SimpleRepository\\Search\\Filters\\SearchFilter/g' \
  {} +

echo "Migration complete. Please review the changes and test your application."
```

## New Features in v5

### 1. Better Organization

- Clear separation of contracts, implementations, and traits
- Improved namespace structure
- Better IDE support

### 2. Enhanced Type Safety

All methods now have proper return type declarations:

```php
public function find($id, array $columns = ['*']): ?Model
public function findOrFail($id, array $columns = ['*']): Model
public function create(array $attributes): Model
public function update($id, array $attributes): Model
```

### 3. Improved Search Functionality

- Better performance for search queries
- More intuitive filter API
- Support for complex nested queries

### 4. Laravel 12 Support

Full compatibility with Laravel 12's latest features and improvements.

## Troubleshooting

### Class Not Found Errors

If you get "Class not found" errors after migration:

1. Clear composer autoload:
   ```bash
   composer dump-autoload
   ```

2. Check for typos in the new namespaces

3. Ensure all imports are updated

### Search Not Working

If search functionality breaks:

1. Verify the trait import is updated:
   ```php
   use ElegantMedia\SimpleRepository\Search\Traits\SearchableLike;
   ```

2. Clear application caches:
   ```bash
   php artisan cache:clear
   ```

### Custom Repository Methods

Custom methods in your repositories should continue to work without changes. Only the base class name and imports need updating.

## Rollback Plan

If you need to rollback to v4:

1. Revert your code changes
2. Update composer.json:
   ```json
   "elegantmedia/laravel-simple-repository": "^4.0"
   ```
3. Run:
   ```bash
   composer update
   ```

## Getting Help

If you encounter issues during migration:

1. Check the [GitHub Issues](https://github.com/elegantmedia/Laravel-Simple-Repository/issues)
2. Review the [examples](USAGE.md) in the documentation
3. Open a new issue with details about your migration problem