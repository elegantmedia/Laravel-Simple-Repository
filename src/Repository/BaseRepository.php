<?php

declare(strict_types=1);

namespace ElegantMedia\SimpleRepository\Repository;

use ElegantMedia\SimpleRepository\Contracts\RepositoryInterface;
use ElegantMedia\SimpleRepository\Exceptions\KeyNotFoundInAttributesException;
use ElegantMedia\SimpleRepository\Search\Contracts\FilterableInterface;
use ElegantMedia\SimpleRepository\Search\Filters\SearchFilter;
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
	 * Whether to automatically wrap write operations in transactions.
	 */
	protected bool $autoTransaction = false;

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
	public function all(array $with = []): Collection
	{
		$query = $this->newQuery();

		$eagerLoad = $with;
		if (!empty($eagerLoad)) {
			$query->with($eagerLoad);
		}

		return $query->get();
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
	public function search(?FilterableInterface $filter = null): LengthAwarePaginator|Collection
	{
		if ($filter === null) {
			$filter = $this->newFilter();
		}

		// Check if the filter is a SearchFilter instance that has a get() method
		if ($filter instanceof \ElegantMedia\SimpleRepository\Search\Filters\SearchFilter) {
			return $filter->get();
		}

		// Fallback for other filter implementations
		$query = $this->newQuery();
		$filter->apply($query);

		if ($filter->shouldPaginate()) {
			return $query->paginate($filter->getPerPage());
		}

		return $query->get();
	}

	/**
	 * {@inheritdoc}
	 */
	public function searchByTerm(string $term): Collection
	{
		$filter = $this->newFilter();
		$filter->setKeyword($term);
		$filter->setPaginate(false);

		return $this->search($filter);
	}

	/**
	 * {@inheritdoc}
	 */
	public function searchPaginated(string $term, int $perPage = 50): LengthAwarePaginator
	{
		$filter = $this->newFilter();
		$filter->setKeyword($term);
		$filter->setPerPage($perPage);

		return $this->search($filter);
	}

	/**
	 * {@inheritdoc}
	 */
	public function newFilter(bool $defaults = true): FilterableInterface
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
		return $this->executeInTransaction(function () use ($id, $attributes, $idColumn) {
			if ($id === null) {
				return $this->newQuery()->create($attributes);
			}

			return $this->firstOrCreate([$idColumn => $id], $attributes);
		});
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
			throw (new ModelNotFoundException())->setModel($this->getModelClass());
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
	 | Update or Create
	 |-----------------------------------------------------------
	 */

	/**
	 * {@inheritdoc}
	 */
	public function updateOrCreateById(int|string|null $id, array $attributes, string $idColumn = 'id'): Model
	{
		return $this->executeInTransaction(function () use ($id, $attributes, $idColumn) {
			if ($id === null) {
				return $this->newQuery()->create($attributes);
			}

			return $this->newQuery()->updateOrCreate([$idColumn => $id], $attributes);
		});
	}

	/**
	 * {@inheritdoc}
	 */
	public function updateOrCreateByUuid(array $attributes): Model
	{
		return $this->executeInTransaction(function () use ($attributes) {
			if (!array_key_exists('uuid', $attributes)) {
				throw new KeyNotFoundInAttributesException(
					"Key 'uuid' not found in the given attributes array"
				);
			}

			return $this->updateOrCreateById($attributes['uuid'], $attributes, 'uuid');
		});
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
		return $this->executeInTransaction(function () use ($model, $attributes) {
			return $model->fill($attributes)->save();
		});
	}

	/**
	 * {@inheritdoc}
	 */
	public function updateById(int|string $id, array $attributes, string $idColumn = 'id'): bool
	{
		return $this->executeInTransaction(function () use ($id, $attributes, $idColumn) {
			$model = $this->findByField($idColumn, $id);

			if ($model === null) {
				return false;
			}

			return $model->fill($attributes)->save();
		});
	}

	/**
	 * {@inheritdoc}
	 */
	public function updateWhere(array $where, array $data): int
	{
		return $this->executeInTransaction(function () use ($where, $data) {
			$query = $this->newQuery();
			$this->applyWhereConditions($query, $where);

			return $query->update($data);
		});
	}

	/**
	 * {@inheritdoc}
	 */
	public function save(Model $model): bool
	{
		return $this->executeInTransaction(function () use ($model) {
			return $model->save();
		});
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
		return $this->executeInTransaction(function () use ($id) {
			$model = $this->find($id);

			if ($model === null) {
				return false;
			}

			return (bool) $model->delete();
		});
	}

	/**
	 * {@inheritdoc}
	 */
	public function deleteWhere(array $where): int
	{
		return $this->executeInTransaction(function () use ($where) {
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
		});
	}

	/**
	 * {@inheritdoc}
	 */
	public function deleteManyByIds(array $ids): int
	{
		return $this->executeInTransaction(function () use ($ids) {
			return $this->newQuery()->whereIn($this->primaryKey, $ids)->delete();
		});
	}

	/**
	 * {@inheritdoc}
	 */
	public function restore(int|string $id): bool
	{
		return $this->executeInTransaction(function () use ($id) {
			$model = $this->newQuery()->withTrashed()->find($id);

			if ($model === null) {
				return false;
			}

			return (bool) $model->restore();
		});
	}

	/**
	 * {@inheritdoc}
	 */
	public function forceDelete(int|string $id): bool
	{
		return $this->executeInTransaction(function () use ($id) {
			$model = $this->newQuery()->withTrashed()->find($id);

			if ($model === null) {
				return false;
			}

			return (bool) $model->forceDelete();
		});
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
		return $this->executeInTransaction(function () use ($attributes, $values) {
			return $this->newQuery()->firstOrCreate($attributes, $values);
		});
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return Model|Collection<int, Model>|null
	 */
	public function random(int $count = 1): Model|Collection|null
	{
		/** @var Collection<int, Model> $result */
		$result = $this->newQuery()->inRandomOrder()->limit($count)->get();

		if ($count === 1) {
			return $result->first();
		}

		return $result;
	}

	/*
	 |-----------------------------------------------------------
	 | Transaction Methods
	 |-----------------------------------------------------------
	 */

	/**
	 * {@inheritdoc}
	 */
	public function withTransaction(bool $enabled = true): static
	{
		$this->autoTransaction = $enabled;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function transaction(\Closure $callback): mixed
	{
		return $this->model->getConnection()->transaction(function () use ($callback) {
			return $callback($this);
		});
	}

	/**
	 * {@inheritdoc}
	 */
	public function beginTransaction(): void
	{
		$this->model->getConnection()->beginTransaction();
	}

	/**
	 * {@inheritdoc}
	 */
	public function commit(): void
	{
		$this->model->getConnection()->commit();
	}

	/**
	 * {@inheritdoc}
	 */
	public function rollback(): void
	{
		$this->model->getConnection()->rollBack();
	}

	/**
	 * {@inheritdoc}
	 */
	public function transactionLevel(): int
	{
		return $this->model->getConnection()->transactionLevel();
	}

	/**
	 * Execute a callback within a transaction if auto-transaction is enabled.
	 *
	 * @template T
	 *
	 * @param \Closure(): T $callback
	 *
	 * @return T
	 */
	protected function executeInTransaction(\Closure $callback): mixed
	{
		if ($this->autoTransaction) {
			return $this->transaction(fn () => $callback());
		}

		return $callback();
	}
}
