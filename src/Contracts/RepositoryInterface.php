<?php

declare(strict_types=1);

namespace ElegantMedia\SimpleRepository\Contracts;

use ElegantMedia\SimpleRepository\Search\Contracts\FilterableInterface;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as SupportCollection;

interface RepositoryInterface
{
	/**
	 * Create a new instance of the model.
	 */
	public function newModel(): Model;

	/**
	 * Create a new query builder instance.
	 */
	public function newQuery(): Builder;

	/**
	 * Get the fully qualified class name of the model.
	 */
	public function getModelClass(): string;

	/**
	 * Get the model instance.
	 */
	public function getModel(): Model;

	/*
	 |-----------------------------------------------------------
	 | List/Index/Search
	 |-----------------------------------------------------------
	 */

	/**
	 * Get all models with optional relationships and columns.
	 *
	 * @param array<string> $columns
	 * @param array<string> $with
	 *
	 * @return Collection<int, Model>
	 */
	public function all(array $columns = ['*'], array $with = []): Collection;

	/**
	 * Set relationships to eager load.
	 *
	 * @param array<string>|string $relations
	 */
	public function with(array|string $relations): static;

	/**
	 * Paginate the model results with optional relationships.
	 *
	 * @param array<string> $with
	 */
	public function paginate(
		int $perPage = 50,
		array $with = [],
		?FilterableInterface $filter = null
	): LengthAwarePaginator;

	/**
	 * Simple paginate the model results with optional relationships.
	 *
	 * @param array<string> $with
	 */
	public function simplePaginate(
		int $perPage = 50,
		array $with = [],
		?FilterableInterface $filter = null
	): Paginator;

	/**
	 * Cursor paginate the model results.
	 */
	public function cursorPaginate(int $perPage = 50): CursorPaginator;

	/**
	 * Search models using the provided filter.
	 *
	 * @return LengthAwarePaginator|Collection<int, Model>
	 */
	public function search(?FilterableInterface $filter = null): LengthAwarePaginator|Collection;

	/**
	 * Simple search for models by a search term.
	 *
	 * @return Collection<int, Model>
	 */
	public function searchByTerm(string $term): Collection;

	/**
	 * Search models with pagination.
	 */
	public function searchPaginated(string $term, int $perPage = 50): LengthAwarePaginator;

	/**
	 * Get a search query builder.
	 */
	public function searchQuery(string $term): Builder;

	/**
	 * Create a new search filter instance.
	 */
	public function newSearchFilter(bool $defaults = true): FilterableInterface;

	/*
	 |-----------------------------------------------------------
	 | Find
	 |-----------------------------------------------------------
	 */

	/**
	 * Find a model by its primary key.
	 *
	 * @param array<string> $with
	 */
	public function find(int|string $id, array $with = []): ?Model;

	/**
	 * Find a model by UUID.
	 *
	 * @param array<string> $with
	 */
	public function findByUuid(string $uuid, array $with = []): ?Model;

	/**
	 * Find a model by a specific field value.
	 *
	 * @param array<string> $with
	 */
	public function findByField(string $field, mixed $value, array $with = []): ?Model;

	/**
	 * Find or create a model with the given attributes.
	 *
	 * @param array<string, mixed> $searchAttributes
	 * @param array<string, mixed> $additionalAttributes
	 */
	public function findOrCreate(array $searchAttributes, array $additionalAttributes = []): Model;

	/**
	 * Find or create a model by ID.
	 *
	 * @param array<string, mixed> $attributes
	 */
	public function findOrCreateById(int|string|null $id, array $attributes, string $idColumn = 'id'): Model;

	/**
	 * Find a model by its primary key or throw an exception.
	 *
	 * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
	 */
	public function findOrFail(int|string $id): Model;

	/**
	 * Find a model by a specific field value or throw an exception.
	 *
	 * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
	 */
	public function findByFieldOrFail(string $field, mixed $value): Model;

	/**
	 * Find a model by its primary key including soft deleted records.
	 */
	public function findWithTrashed(int|string $id): ?Model;

	/**
	 * Find a model by its primary key from soft deleted records only.
	 */
	public function findOnlyTrashed(int|string $id): ?Model;

	/**
	 * Find multiple models by their primary keys.
	 *
	 * @param array<int|string> $ids
	 *
	 * @return Collection<int, Model>
	 */
	public function findMany(array $ids): Collection;

	/**
	 * Find multiple models by a specific field value.
	 *
	 * @return Collection<int, Model>
	 */
	public function findManyByField(string $field, mixed $value): Collection;

	/**
	 * Find and update model attributes.
	 *
	 * @param array<string, mixed> $attributes
	 */
	public function findByAttribute(
		string $whereKey,
		mixed $whereValue,
		array $attributes
	): ?Model;

	/*
	 |-----------------------------------------------------------
	 | Create
	 |-----------------------------------------------------------
	 */

	/**
	 * Create a new model instance.
	 *
	 * @param array<string, mixed> $attributes
	 */
	public function create(array $attributes): Model;

	/**
	 * Create multiple model instances.
	 *
	 * @param array<array<string, mixed>> $records
	 *
	 * @return Collection<int, Model>
	 */
	public function createMany(array $records): Collection;

	/**
	 * Update or create a model matching the attributes, and fill it with values.
	 *
	 * @param array<string, mixed> $attributes
	 * @param array<string, mixed> $values
	 */
	public function updateOrCreate(array $attributes, array $values = []): Model;

	/*
	 |-----------------------------------------------------------
	 | Update or Insert/Create
	 |-----------------------------------------------------------
	 */

	/**
	 * Update or insert a model.
	 *
	 * @param array<string, mixed> $searchAttributes
	 * @param array<string, mixed> $values
	 */
	public function updateOrInsert(array $searchAttributes, array $values = []): Model;

	/**
	 * Update or insert a model by ID.
	 *
	 * @param array<string, mixed> $attributes
	 */
	public function updateOrInsertById(int|string|null $id, array $attributes, string $idColumn = 'id'): Model;

	/**
	 * Update or insert a model by UUID.
	 *
	 * @param array<string, mixed> $attributes
	 */
	public function updateOrInsertByUuid(array $attributes): Model;

	/*
	 |-----------------------------------------------------------
	 | Save/Update
	 |-----------------------------------------------------------
	 */

	/**
	 * Update a model instance.
	 *
	 * @param array<string, mixed> $attributes
	 */
	public function updateModel(Model $model, array $attributes): bool;

	/**
	 * Update a model by ID.
	 *
	 * @param array<string, mixed> $attributes
	 */
	public function updateById(int|string $id, array $attributes, string $idColumn = 'id'): bool;

	/**
	 * Update models matching the given conditions.
	 *
	 * @param array<string, mixed> $where
	 * @param array<string, mixed> $data
	 */
	public function updateWhere(array $where, array $data): int;

	/**
	 * Save a model instance.
	 */
	public function save(Model $model): bool;

	/*
	 |-----------------------------------------------------------
	 | Destroy
	 |-----------------------------------------------------------
	 */

	/**
	 * Delete a model by ID.
	 */
	public function delete(int|string $id): bool;

	/**
	 * Delete models matching the given conditions.
	 *
	 * @param array<string, mixed> $where
	 */
	public function deleteWhere(array $where): int;

	/**
	 * Delete multiple models by their primary keys.
	 *
	 * @param array<int|string> $ids
	 */
	public function deleteMany(array $ids): int;

	/**
	 * Restore a soft deleted model by ID.
	 */
	public function restore(int|string $id): bool;

	/**
	 * Force delete a model by ID.
	 */
	public function forceDelete(int|string $id): bool;

	/*
	 |-----------------------------------------------------------
	 | Query Builder Methods
	 |-----------------------------------------------------------
	 */

	/**
	 * Add a where in condition to the query.
	 *
	 * @param array<mixed> $values
	 */
	public function whereIn(string $field, array $values): static;

	/**
	 * Add a where condition to the query.
	 */
	public function where(string $field, mixed $operator, mixed $value = null): static;

	/**
	 * Add a where has condition to the query.
	 */
	public function whereHas(string $relation, ?callable $callback = null): static;

	/**
	 * Add a where doesn't have condition to the query.
	 */
	public function whereDoesntHave(string $relation, ?callable $callback = null): static;

	/**
	 * Add a has condition to the query.
	 */
	public function has(string $relation, string $operator = '>=', int $count = 1): static;

	/**
	 * Add an order by clause to the query.
	 */
	public function orderBy(string $field, string $direction = 'asc'): static;

	/**
	 * Limit the number of results.
	 */
	public function limit(int $limit): static;

	/**
	 * Select specific columns.
	 *
	 * @param array<string>|string $columns
	 */
	public function select(array|string $columns): static;

	/**
	 * Get the results based on the built query.
	 *
	 * @return Collection<int, Model>
	 */
	public function get(): Collection;

	/**
	 * Get the first result based on the built query.
	 */
	public function first(): ?Model;

	/*
	 |-----------------------------------------------------------
	 | Aggregate Methods
	 |-----------------------------------------------------------
	 */

	/**
	 * Get the sum of a column.
	 *
	 * @param array<string, mixed> $where
	 */
	public function sum(string $column, array $where = []): mixed;

	/**
	 * Get the average of a column.
	 *
	 * @param array<string, mixed> $where
	 */
	public function avg(string $column, array $where = []): mixed;

	/**
	 * Get the minimum value of a column.
	 *
	 * @param array<string, mixed> $where
	 */
	public function min(string $column, array $where = []): mixed;

	/**
	 * Get the maximum value of a column.
	 *
	 * @param array<string, mixed> $where
	 */
	public function max(string $column, array $where = []): mixed;

	/*
	 |-----------------------------------------------------------
	 | Utility Methods
	 |-----------------------------------------------------------
	 */

	/**
	 * Check if a model exists with the given conditions.
	 *
	 * @param array<string, mixed> $where
	 */
	public function exists(array $where = []): bool;

	/**
	 * Count models matching the given conditions.
	 *
	 * @param array<string, mixed> $where
	 */
	public function count(array $where = []): int;

	/**
	 * Get a single column's value from the first result.
	 *
	 * @param array<string, mixed> $where
	 */
	public function value(string $column, array $where = []): mixed;

	/**
	 * Get an array with the values of a given column.
	 *
	 * @param array<string, mixed> $where
	 *
	 * @return SupportCollection<int|string, mixed>
	 */
	public function pluck(string $column, array $where = [], ?string $key = null): SupportCollection;

	/**
	 * Chunk the results of the query.
	 *
	 * @param array<string, mixed> $where
	 */
	public function chunk(int $count, callable $callback, array $where = []): bool;

	/**
	 * Get the first model matching the attributes or instantiate it.
	 *
	 * @param array<string, mixed> $attributes
	 * @param array<string, mixed> $values
	 */
	public function firstOrNew(array $attributes, array $values = []): Model;

	/**
	 * Get the first model matching the attributes or create it.
	 *
	 * @param array<string, mixed> $attributes
	 * @param array<string, mixed> $values
	 */
	public function firstOrCreate(array $attributes, array $values = []): Model;

	/**
	 * Get random models.
	 *
	 * @return Model|Collection<int, Model>|null
	 */
	public function random(int $count = 1): Model|Collection|null;

	/**
	 * Check if models exist matching the given conditions.
	 *
	 * @param array<string, mixed> $where
	 */
	public function existsWhere(array $where): bool;

	/**
	 * Count models matching the given conditions.
	 *
	 * @param array<string, mixed> $where
	 */
	public function countWhere(array $where): int;
}
