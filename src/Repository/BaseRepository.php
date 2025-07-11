<?php

declare(strict_types=1);

namespace ElegantMedia\SimpleRepository\Repository;

use ElegantMedia\SimpleRepository\Contracts\RepositoryInterface;
use ElegantMedia\SimpleRepository\Exceptions\KeyNotFoundInAttributesException;
use ElegantMedia\SimpleRepository\Search\Contracts\FilterableInterface;
use ElegantMedia\SimpleRepository\Search\Filters\SearchFilter;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection as SupportCollection;

abstract class BaseRepository implements RepositoryInterface
{
	protected string $primaryKey = 'id';

	protected Model $model;

	/**
	 * @var array<string>
	 */
	protected array $with = [];

	/**
	 * Query builder instance for chaining methods.
	 */
	protected ?Builder $query = null;

	public function __construct(Model $model)
	{
		$this->model = $model;
	}

	/**
	 * {@inheritdoc}
	 */
	public function newModel(): Model
	{
		return clone $this->model;
	}

	/**
	 * {@inheritdoc}
	 */
	public function newQuery(): Builder
	{
		return $this->model->newQuery();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getModelClass(): string
	{
		return get_class($this->model);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getModel(): Model
	{
		return $this->model;
	}

	/*
	 |-----------------------------------------------------------
	 | List/Index/Search
	 |-----------------------------------------------------------
	 */

	/**
	 * {@inheritdoc}
	 */
	public function all(array $columns = ['*'], array $with = []): Collection
	{
		$query = $this->newQuery();

		$eagerLoad = array_merge($this->with, $with);
		if (!empty($eagerLoad)) {
			$query->with($eagerLoad);
		}

		return $query->get($columns);
	}

	/**
	 * {@inheritdoc}
	 */
	public function with(array|string $relations): static
	{
		$this->with = is_array($relations) ? $relations : [$relations];

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function paginate(
		int $perPage = 50,
		array $with = [],
		?FilterableInterface $filter = null
	): LengthAwarePaginator {
		$query = $this->newQuery();

		if (!empty($with)) {
			$query->with($with);
		}

		if ($filter !== null) {
			$filter->apply($query);
		}

		return $query->paginate($perPage);
	}

	/**
	 * {@inheritdoc}
	 */
	public function simplePaginate(
		int $perPage = 50,
		array $with = [],
		?FilterableInterface $filter = null
	): Paginator {
		$query = $this->newQuery();

		if (!empty($with)) {
			$query->with($with);
		}

		if ($filter !== null) {
			$filter->apply($query);
		}

		return $query->simplePaginate($perPage);
	}

	/**
	 * {@inheritdoc}
	 */
	public function cursorPaginate(int $perPage = 15): CursorPaginator
	{
		return $this->newQuery()->cursorPaginate($perPage);
	}

	/**
	 * {@inheritdoc}
	 */
	public function search(?FilterableInterface $filter = null): LengthAwarePaginator|Collection
	{
		if ($filter === null) {
			$filter = $this->newSearchFilter();
		}

		$query = $this->newQuery();
		$filter->apply($query);

		return $filter->get();
	}

	/**
	 * {@inheritdoc}
	 */
	public function searchByTerm(string $term): Collection
	{
		return $this->searchQuery($term)->get();
	}

	/**
	 * {@inheritdoc}
	 */
	public function searchPaginated(string $term, int $perPage = 20): LengthAwarePaginator
	{
		return $this->searchQuery($term)->paginate($perPage);
	}

	/**
	 * {@inheritdoc}
	 */
	public function searchQuery(string $term): Builder
	{
		$query = $this->newQuery();

		// Get searchable fields from the model if available
		$searchableFields = property_exists($this->model, 'searchable')
			? $this->model->searchable
			: [$this->model->getKeyName()];

		$query->where(function ($q) use ($term, $searchableFields) {
			foreach ($searchableFields as $field) {
				$q->orWhere($field, 'LIKE', '%' . $term . '%');
			}
		});

		return $query;
	}

	/**
	 * {@inheritdoc}
	 */
	public function newSearchFilter(bool $defaults = true): FilterableInterface
	{
		return new SearchFilter($this->newQuery(), $defaults);
	}

	/*
	 |-----------------------------------------------------------
	 | Find
	 |-----------------------------------------------------------
	 */

	/**
	 * {@inheritdoc}
	 */
	public function find(int|string $id, array $with = []): ?Model
	{
		$query = $this->newQuery();

		if (!empty($with)) {
			$query->with($with);
		}

		return $query->find($id);
	}

	/**
	 * {@inheritdoc}
	 */
	public function findByUuid(string $uuid, array $with = []): ?Model
	{
		return $this->findByField('uuid', $uuid, $with);
	}

	/**
	 * {@inheritdoc}
	 */
	public function findByField(string $field, mixed $value, array $with = []): ?Model
	{
		$query = $this->newQuery();

		if (!empty($with)) {
			$query->with($with);
		}

		return $query->where($field, $value)->first();
	}

	/**
	 * {@inheritdoc}
	 */
	public function findOrCreate(array $searchAttributes, array $additionalAttributes = []): Model
	{
		return $this->firstOrCreate($searchAttributes, $additionalAttributes);
	}

	/**
	 * {@inheritdoc}
	 */
	public function findOrCreateById(int|string|null $id, array $attributes, string $idColumn = 'id'): Model
	{
		if ($id === null) {
			return $this->create($attributes);
		}

		return $this->findOrCreate([$idColumn => $id], $attributes);
	}

	/**
	 * {@inheritdoc}
	 */
	public function findOrFail(int|string $id): Model
	{
		$model = $this->find($id);

		if ($model === null) {
			throw (new ModelNotFoundException())->setModel($this->getModelClass(), [$id]);
		}

		return $model;
	}

	/**
	 * {@inheritdoc}
	 */
	public function findByFieldOrFail(string $field, mixed $value): Model
	{
		$model = $this->findByField($field, $value);

		if ($model === null) {
			throw (new ModelNotFoundException())->setModel($this->getModelClass(), [$field => $value]);
		}

		return $model;
	}

	/**
	 * {@inheritdoc}
	 */
	public function findWithTrashed(int|string $id): ?Model
	{
		return $this->newQuery()->withTrashed()->find($id);
	}

	/**
	 * {@inheritdoc}
	 */
	public function findOnlyTrashed(int|string $id): ?Model
	{
		return $this->newQuery()->onlyTrashed()->find($id);
	}

	/**
	 * {@inheritdoc}
	 */
	public function findMany(array $ids): Collection
	{
		return $this->newQuery()->findMany($ids);
	}

	/**
	 * {@inheritdoc}
	 */
	public function findManyByField(string $field, mixed $value): Collection
	{
		return $this->newQuery()->where($field, $value)->get();
	}

	/**
	 * {@inheritdoc}
	 */
	public function findByAttribute(
		string $whereKey,
		mixed $whereValue,
		array $attributes
	): ?Model {
		$model = $this->findByField($whereKey, $whereValue);

		if ($model !== null) {
			$model->fill($attributes);
			$model->save();
		}

		return $model;
	}

	/*
	 |-----------------------------------------------------------
	 | Create
	 |-----------------------------------------------------------
	 */

	/**
	 * {@inheritdoc}
	 */
	public function create(array $attributes): Model
	{
		return $this->newQuery()->create($attributes);
	}

	/**
	 * {@inheritdoc}
	 */
	public function createMany(array $records): Collection
	{
		$created = new Collection();

		foreach ($records as $record) {
			$created->push($this->create($record));
		}

		return $created;
	}

	/**
	 * {@inheritdoc}
	 */
	public function updateOrCreate(array $attributes, array $values = []): Model
	{
		return $this->newQuery()->updateOrCreate($attributes, $values);
	}

	/*
	 |-----------------------------------------------------------
	 | Update or Insert/Create
	 |-----------------------------------------------------------
	 */

	/**
	 * {@inheritdoc}
	 */
	public function updateOrInsert(array $searchAttributes, array $values = []): Model
	{
		return $this->updateOrCreate($searchAttributes, $values);
	}

	/**
	 * {@inheritdoc}
	 */
	public function updateOrInsertById(int|string|null $id, array $attributes, string $idColumn = 'id'): Model
	{
		if ($id === null) {
			return $this->create($attributes);
		}

		return $this->updateOrInsert([$idColumn => $id], $attributes);
	}

	/**
	 * {@inheritdoc}
	 */
	public function updateOrInsertByUuid(array $attributes): Model
	{
		if (!array_key_exists('uuid', $attributes)) {
			throw new KeyNotFoundInAttributesException(
				"Key 'uuid' not found in the given attributes array"
			);
		}

		return $this->updateOrInsertById($attributes['uuid'], $attributes, 'uuid');
	}

	/*
	 |-----------------------------------------------------------
	 | Save/Update
	 |-----------------------------------------------------------
	 */

	/**
	 * {@inheritdoc}
	 */
	public function updateModel(Model $model, array $attributes): bool
	{
		return $model->fill($attributes)->save();
	}

	/**
	 * {@inheritdoc}
	 */
	public function updateById(int|string $id, array $attributes, string $idColumn = 'id'): bool
	{
		$model = $this->findByField($idColumn, $id);

		if ($model === null) {
			return false;
		}

		return $this->updateModel($model, $attributes);
	}

	/**
	 * {@inheritdoc}
	 */
	public function updateWhere(array $where, array $data): int
	{
		$query = $this->newQuery();
		$this->applyWhereConditions($query, $where);

		return $query->update($data);
	}

	/**
	 * {@inheritdoc}
	 */
	public function save(Model $model): bool
	{
		return $model->save();
	}

	/*
	 |-----------------------------------------------------------
	 | Destroy
	 |-----------------------------------------------------------
	 */

	/**
	 * {@inheritdoc}
	 */
	public function delete(int|string $id): bool
	{
		$model = $this->find($id);

		if ($model === null) {
			return false;
		}

		return (bool) $model->delete();
	}

	/**
	 * {@inheritdoc}
	 */
	public function deleteWhere(array $where): int
	{
		$query = $this->newQuery();

		foreach ($where as $field => $value) {
			if (is_array($value)) {
				[$field, $operator, $value] = $value;
				$query->where($field, $operator, $value);
			} else {
				$query->where($field, $value);
			}
		}

		return $query->delete();
	}

	/**
	 * {@inheritdoc}
	 */
	public function deleteMany(array $ids): int
	{
		return $this->newQuery()->whereIn($this->primaryKey, $ids)->delete();
	}

	/**
	 * {@inheritdoc}
	 */
	public function restore(int|string $id): bool
	{
		$model = $this->newQuery()->withTrashed()->find($id);

		if ($model === null) {
			return false;
		}

		return (bool) $model->restore();
	}

	/**
	 * {@inheritdoc}
	 */
	public function forceDelete(int|string $id): bool
	{
		$model = $this->newQuery()->withTrashed()->find($id);

		if ($model === null) {
			return false;
		}

		return (bool) $model->forceDelete();
	}

	/*
	 |-----------------------------------------------------------
	 | Query Builder Methods
	 |-----------------------------------------------------------
	 */

	/**
	 * Get or initialize the query builder.
	 */
	protected function getQueryBuilder(): Builder
	{
		if ($this->query === null) {
			$this->query = $this->newQuery();
		}

		return $this->query;
	}

	/**
	 * Reset the query builder.
	 */
	protected function resetQuery(): void
	{
		$this->query = null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function whereIn(string $field, array $values): static
	{
		$this->getQueryBuilder()->whereIn($field, $values);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function where(string $field, mixed $operator, mixed $value = null): static
	{
		if ($value === null) {
			$value = $operator;
			$operator = '=';
		}

		$this->getQueryBuilder()->where($field, $operator, $value);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function whereHas(string $relation, ?callable $callback = null): static
	{
		$this->getQueryBuilder()->whereHas($relation, $callback);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function whereDoesntHave(string $relation, ?callable $callback = null): static
	{
		$this->getQueryBuilder()->whereDoesntHave($relation, $callback);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has(string $relation, string $operator = '>=', int $count = 1): static
	{
		$this->getQueryBuilder()->has($relation, $operator, $count);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function orderBy(string $field, string $direction = 'asc'): static
	{
		$this->getQueryBuilder()->orderBy($field, $direction);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function limit(int $limit): static
	{
		$this->getQueryBuilder()->limit($limit);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function select(array|string $columns): static
	{
		$columns = is_array($columns) ? $columns : func_get_args();
		$this->getQueryBuilder()->select($columns);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get(): Collection
	{
		$result = $this->getQueryBuilder()->get();
		$this->resetQuery();

		return $result;
	}

	/**
	 * {@inheritdoc}
	 */
	public function first(): ?Model
	{
		$result = $this->getQueryBuilder()->first();
		$this->resetQuery();

		return $result;
	}

	/*
	 |-----------------------------------------------------------
	 | Aggregate Methods
	 |-----------------------------------------------------------
	 */

	/**
	 * Apply where conditions to a query.
	 *
	 * @param Builder $query
	 * @param array   $where
	 *
	 * @return Builder
	 */
	protected function applyWhereConditions(Builder $query, array $where): Builder
	{
		foreach ($where as $field => $value) {
			if (is_array($value) && count($value) === 2) {
				// Handle ['field' => ['operator', 'value']] format
				[$operator, $operatorValue] = $value;
				$query->where($field, $operator, $operatorValue);
			} else {
				$query->where($field, $value);
			}
		}

		return $query;
	}

	/**
	 * {@inheritdoc}
	 */
	public function sum(string $column, array $where = []): mixed
	{
		$query = $this->newQuery();
		$this->applyWhereConditions($query, $where);

		return $query->sum($column);
	}

	/**
	 * {@inheritdoc}
	 */
	public function avg(string $column, array $where = []): mixed
	{
		$query = $this->newQuery();
		$this->applyWhereConditions($query, $where);

		return $query->avg($column);
	}

	/**
	 * {@inheritdoc}
	 */
	public function min(string $column, array $where = []): mixed
	{
		$query = $this->newQuery();
		$this->applyWhereConditions($query, $where);

		return $query->min($column);
	}

	/**
	 * {@inheritdoc}
	 */
	public function max(string $column, array $where = []): mixed
	{
		$query = $this->newQuery();
		$this->applyWhereConditions($query, $where);

		return $query->max($column);
	}

	/*
	 |-----------------------------------------------------------
	 | Utility Methods
	 |-----------------------------------------------------------
	 */

	/**
	 * {@inheritdoc}
	 */
	public function exists(array $where = []): bool
	{
		$query = $this->newQuery();
		$this->applyWhereConditions($query, $where);

		return $query->exists();
	}

	/**
	 * {@inheritdoc}
	 */
	public function count(array $where = []): int
	{
		$query = $this->newQuery();
		$this->applyWhereConditions($query, $where);

		return $query->count();
	}

	/**
	 * {@inheritdoc}
	 */
	public function value(string $column, array $where = []): mixed
	{
		$query = $this->newQuery();

		foreach ($where as $field => $value) {
			$query->where($field, $value);
		}

		return $query->value($column);
	}

	/**
	 * {@inheritdoc}
	 */
	public function pluck(string $column, array $where = [], ?string $key = null): SupportCollection
	{
		$query = $this->newQuery();

		foreach ($where as $field => $value) {
			$query->where($field, $value);
		}

		return $query->pluck($column, $key);
	}

	/**
	 * {@inheritdoc}
	 */
	public function chunk(int $count, callable $callback, array $where = []): bool
	{
		$query = $this->newQuery();

		foreach ($where as $field => $value) {
			$query->where($field, $value);
		}

		return $query->chunk($count, $callback);
	}

	/**
	 * {@inheritdoc}
	 */
	public function firstOrNew(array $attributes, array $values = []): Model
	{
		return $this->newQuery()->firstOrNew($attributes, $values);
	}

	/**
	 * {@inheritdoc}
	 */
	public function firstOrCreate(array $attributes, array $values = []): Model
	{
		return $this->newQuery()->firstOrCreate($attributes, $values);
	}

	/**
	 * {@inheritdoc}
	 */
	public function random(int $count = 1): Model|Collection|null
	{
		$result = $this->newQuery()->inRandomOrder()->limit($count)->get();

		if ($count === 1) {
			return $result->first();
		}

		return $result;
	}

	/**
	 * {@inheritdoc}
	 */
	public function existsWhere(array $where): bool
	{
		return $this->exists($where);
	}

	/**
	 * {@inheritdoc}
	 */
	public function countWhere(array $where): int
	{
		return $this->count($where);
	}
}
