# Development Guide


## Testing

### Running Tests

```bash
# Run all tests
composer test

# Run specific test file
vendor/bin/phpunit tests/Unit/Repository/BaseRepositoryTest.php

# Run specific test method
vendor/bin/phpunit --filter test_create_creates_new_model

# Run with code coverage (requires Xdebug or PCOV)
vendor/bin/phpunit --coverage-html build/coverage
```

### Test Coverage

The package includes comprehensive tests covering:

- **BaseRepository**: Model instantiation, list/index methods, eager loading
- **CRUD Operations**: Create, update, save, and delete methods
- **Find Methods**: Various lookup strategies with exception handling
- **Search & Filtering**: Custom filters, sorting, and pagination
- **Utility Methods**: exists(), count(), pluck(), chunk(), and more
- **Transactions**: Automatic wrapping, manual control, callbacks, and nested transactions

Tests use an in-memory SQLite database for fast, isolated test execution.
