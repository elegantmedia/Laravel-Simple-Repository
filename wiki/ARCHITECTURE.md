# Laravel Simple Repository - Developer Guide

## Architecture Overview

The Laravel Simple Repository package implements a clean repository pattern with a filter-based query building approach. This guide explains the high-level architecture decisions and design philosophy for developers maintaining or extending this package.

## Core Design Principles

### 1. Separation of Concerns
- **Repository**: Handles data access and persistence operations
- **Filter**: Manages query building and filtering logic
- **Model**: Represents the domain entity

### 2. No Method Duplication
The architecture avoids duplicating Laravel's Eloquent Builder methods across interfaces. Query building methods are accessed through:
- The Filter class for search operations
- Direct query builder access when needed

### 3. Composition Over Inheritance
The SearchFilter class uses **composition** rather than extending Laravel's Builder class. This decision was made to:
- Avoid method signature conflicts
- Maintain cleaner separation between filter logic and query building
- Provide more flexibility in method implementations
- Allow for easier testing and mocking

## Key Architecture Decisions

### Why Not Extend Laravel's Builder?

Initially, extending Laravel's Builder class seemed like a natural choice for the SearchFilter implementation. However, this approach led to several issues:

1. **Method Signature Conflicts**: Laravel's Builder has specific method signatures that don't always align with filter-specific needs
2. **Tight Coupling**: Extending Builder would tightly couple our filter logic to Laravel's implementation details
3. **Maintenance Burden**: Changes in Laravel's Builder class could break our implementation
4. **Testing Complexity**: Mocking and testing becomes more complex when extending framework classes

### The Composition Approach

Instead, SearchFilter maintains an instance of Laravel's Builder and proxies calls as needed. This provides:

1. **Flexibility**: We can modify method behavior without being constrained by parent class signatures
2. **Stability**: Our API remains stable even if Laravel's internals change
3. **Clarity**: Clear distinction between filter operations and query building
4. **Testability**: Easier to mock and test in isolation

### Deferred Query Execution

The SearchFilter stores query conditions and applies them only when results are requested. This design:

1. **Prevents Double Application**: Conditions aren't applied multiple times
2. **Enables Reusability**: Filters can be configured once and used multiple times  
3. **Maintains State**: Filter state is preserved throughout configuration
4. **Improves Performance**: Queries are only built when needed

## Component Relationships

```
┌─────────────────────┐
│ RepositoryInterface │
└──────────┬──────────┘
           │ implements
           ▼
┌─────────────────────┐         ┌────────────────────┐
│   BaseRepository    │────────▶│ FilterableInterface│
└──────────┬──────────┘  uses   └────────────────────┘
           │                              ▲
           │                              │ implements
           ▼                              │
┌─────────────────────┐         ┌──────────────────┐
│   Laravel Model     │         │   SearchFilter   │
└─────────────────────┘         └────────┬─────────┘
                                         │
                                         ▼ uses (composition)
                                ┌─────────────────┐
                                │ Laravel Builder │
                                └─────────────────┘
```

## Design Patterns

### Repository Pattern
Provides an abstraction layer between your application and data persistence, making it easier to:
- Switch data sources
- Mock data access in tests
- Centralize query logic
- Apply consistent data access rules

### Builder Pattern
The SearchFilter implements a variant of the builder pattern, allowing:
- Fluent interface for query construction
- Step-by-step query building
- Stateful query configuration
- Deferred execution

### Strategy Pattern
Different filter implementations can be created for different search strategies, all implementing the same FilterableInterface.

## Interface Distinction: RepositoryInterface vs FilterableInterface

### RepositoryInterface - CRUD and Search Operations
The RepositoryInterface is focused on data persistence and retrieval operations:

**Core Responsibilities:**
- **Create**: `create()`, `createMany()`, `firstOrCreate()`
- **Read**: `find()`, `findMany()`, `all()`, `paginate()`
- **Update**: `updateModel()`, `updateById()`, `updateWhere()`
- **Delete**: `delete()`, `deleteWhere()`, `restore()`, `forceDelete()`
- **Search**: `search()`, `searchByTerm()`, `searchPaginated()`
- **Aggregates**: `sum()`, `avg()`, `min()`, `max()`, `count()`
- **Utilities**: `exists()`, `pluck()`, `chunk()`, `random()`

**Key Characteristics:**
- Operates on complete datasets
- Returns fully hydrated models or collections
- Handles model lifecycle (creation, updates, deletion)
- Manages relationships and eager loading
- Provides high-level abstractions for common operations

### FilterableInterface - Query Building
The FilterableInterface is dedicated to constructing complex queries:

**Core Responsibilities:**
- **Query Construction**: Build complex WHERE clauses, joins, and conditions
- **Search Configuration**: Set search terms and fields to search
- **Sorting**: Configure sort fields and directions
- **Pagination**: Control pagination settings
- **Query Modification**: Apply conditions to existing query builders

**Key Characteristics:**
- Stateful query building (stores conditions for later application)
- Chainable/fluent interface for query construction
- Deferred execution (queries built but not executed)
- Reusable query configurations
- No direct database interaction

### Working Together

The repository uses filters to build complex queries:

```php
// Repository handles the operation
$results = $repository->search($filter);

// Filter handles query construction
$filter = $repository->newFilter()
    ->setKeyword('Laravel')
    ->setSortBy('created_at')
    ->setPaginate(true);
```

### When to Use Each

**Use RepositoryInterface when:**
- Performing CRUD operations
- Need complete model instances
- Working with model relationships
- Performing aggregations
- Need repository-level caching or events

**Use FilterableInterface when:**
- Building complex search queries
- Need reusable query configurations
- Implementing advanced filtering logic
- Creating dynamic queries based on user input
- Need to modify queries before execution

## Extension Philosophy

### Open/Closed Principle
The architecture is open for extension but closed for modification:
- New filter types can be created by implementing FilterableInterface
- Custom repository methods can be added by extending BaseRepository
- Query builder capabilities remain accessible without modifying core interfaces

### Interface Segregation
Interfaces are kept focused and minimal:
- Repository interface contains only repository-specific operations
- Filter interface contains only filter-specific operations
- No "fat" interfaces with dozens of methods

## Testing Strategy

### Repository Testing
- Test repository methods with real database interactions
- Use filters for complex query testing
- Access query builder directly for edge cases

### Filter Testing
- Test filter configuration independently
- Verify condition storage and application
- Test interaction with query builder

### Integration Testing
- Test complete workflows from repository through filter to results
- Verify pagination, sorting, and filtering work together
- Ensure proper SQL generation

## Performance Considerations

### Query Optimization
- Filters allow for query analysis before execution
- Conditions can be optimized before applying to builder
- Direct query builder access ensures no performance overhead for complex queries

### Memory Efficiency
- Deferred execution prevents unnecessary query building
- Condition storage is lightweight
- No query result caching by default (can be added if needed)

## Conclusion

This architecture provides a clean, maintainable, and extensible foundation for repository-based data access in Laravel applications. By focusing on composition, interface segregation, and clear separation of concerns, the package remains flexible while providing a stable API for developers.
