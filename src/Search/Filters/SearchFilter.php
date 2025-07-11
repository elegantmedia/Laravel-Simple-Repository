<?php

declare(strict_types=1);

namespace ElegantMedia\SimpleRepository\Search\Filters;

use ElegantMedia\SimpleRepository\Search\Contracts\FilterableInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class SearchFilter implements FilterableInterface
{
	protected Builder $query;

	protected bool $shouldPaginate = true;

	protected int $perPage = 50;

	protected ?string $keyword = null;

	protected string $sortBy = 'created_at';

	protected string $sortDirection = 'desc';

	/** @var array<string> */
	protected array $with = [];

	/** @var array<array{field: string, operator: mixed, value: mixed}> */
	protected array $wheres = [];

	/** @var array<array{relation: string, callback: callable}> */
	protected array $whereHas = [];

	/** @var array<array{field: string, values: array}> */
	protected array $whereIns = [];

	/** @var array<string> */
	protected array $whereNulls = [];

	/** @var array<string> */
	protected array $whereNotNulls = [];

	/** @var array<array{field: string, range: array}> */
	protected array $whereBetweens = [];

	/** @var array<array{field: string, direction: string}> */
	protected array $orderBys = [];

	protected ?Request $request = null;

	public function __construct(Builder $query, bool $defaults = true)
	{
		$this->query = $query;

		if ($defaults) {
			$this->applyDefaults();
		}
	}

	/**
	 * Set the request instance for extracting parameters.
	 */
	public function setRequest(Request $request): self
	{
		$this->request = $request;

		return $this;
	}

	/**
	 * Apply default settings from request if available.
	 */
	protected function applyDefaults(): void
	{
		if ($this->request !== null) {
			$this->keyword = $this->request->input('q');

			if ($this->request->has('sort_by')) {
				$this->sortBy = $this->request->input('sort_by', $this->sortBy);
			}

			if ($this->request->has('sort_direction')) {
				$direction = strtolower($this->request->input('sort_direction', $this->sortDirection));
				$this->sortDirection = in_array($direction, ['asc', 'desc']) ? $direction : 'desc';
			}

			if ($this->request->has('per_page')) {
				$perPage = (int) $this->request->input('per_page', $this->perPage);
				$this->perPage = min(max($perPage, 1), 100); // Limit between 1 and 100
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function apply(Builder $query): Builder
	{
		// Apply search keyword if model is searchable
		if ($this->keyword !== null && method_exists($query->getModel(), 'scopeSearchByKeyword')) {
			$query->searchByKeyword($this->keyword);
		}

		// Apply eager loading
		if (!empty($this->with)) {
			$query->with($this->with);
		}

		// Apply where conditions
		foreach ($this->wheres as $where) {
			$query->where($where['field'], $where['operator'], $where['value']);
		}

		// Apply whereHas conditions
		foreach ($this->whereHas as $whereHas) {
			$query->whereHas($whereHas['relation'], $whereHas['callback']);
		}

		// Apply whereIn conditions
		foreach ($this->whereIns as $whereIn) {
			$query->whereIn($whereIn['field'], $whereIn['values']);
		}

		// Apply whereNull conditions
		foreach ($this->whereNulls as $field) {
			$query->whereNull($field);
		}

		// Apply whereNotNull conditions
		foreach ($this->whereNotNulls as $field) {
			$query->whereNotNull($field);
		}

		// Apply whereBetween conditions
		foreach ($this->whereBetweens as $whereBetween) {
			$query->whereBetween($whereBetween['field'], $whereBetween['range']);
		}

		// Apply sorting
		if (!empty($this->orderBys)) {
			foreach ($this->orderBys as $orderBy) {
				$query->orderBy($orderBy['field'], $orderBy['direction']);
			}
		} else {
			// Fallback to original sorting behavior
			$query->orderBy($this->sortBy, $this->sortDirection);
		}

		return $query;
	}

	/**
	 * {@inheritdoc}
	 */
	public function paginate(bool $paginate = true): self
	{
		$this->shouldPaginate = $paginate;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function shouldPaginate(): bool
	{
		return $this->shouldPaginate;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setPerPage(int $perPage): self
	{
		$this->perPage = max(1, min($perPage, 100)); // Limit between 1 and 100

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPerPage(): int
	{
		return $this->perPage;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setKeyword(?string $keyword): self
	{
		$this->keyword = $keyword;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getKeyword(): ?string
	{
		return $this->keyword;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setSortBy(string $field): self
	{
		$this->sortBy = $field;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSortBy(): string
	{
		return $this->sortBy;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setSortDirection(string $direction): self
	{
		$direction = strtolower($direction);
		$this->sortDirection = in_array($direction, ['asc', 'desc']) ? $direction : 'desc';

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSortDirection(): string
	{
		return $this->sortDirection;
	}

	/**
	 * {@inheritdoc}
	 */
	public function with(array|string $relations): self
	{
		if (is_string($relations)) {
			$relations = [$relations];
		}

		$this->with = array_merge($this->with, $relations);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function where(string $field, mixed $operator, mixed $value = null): self
	{
		// If only two arguments are passed, assume equals operator
		if ($value === null) {
			$value = $operator;
			$operator = '=';
		}

		$this->wheres[] = [
			'field' => $field,
			'operator' => $operator,
			'value' => $value,
		];

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function whereHas(string $relation, callable $callback): self
	{
		$this->whereHas[] = [
			'relation' => $relation,
			'callback' => $callback,
		];

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get(): LengthAwarePaginator|Collection
	{
		$this->apply($this->query);

		if ($this->shouldPaginate) {
			return $this->query->paginate($this->perPage);
		}

		return $this->query->get();
	}

	/**
	 * {@inheritdoc}
	 */
	public function setSearchBy(string $term): self
	{
		return $this->setKeyword($term);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setSortOrder(string $direction): self
	{
		return $this->setSortDirection($direction);
	}

	/**
	 * {@inheritdoc}
	 */
	public function whereIn(string $field, array $values): self
	{
		$this->whereIns[] = [
			'field' => $field,
			'values' => $values,
		];

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function whereNull(string $field): self
	{
		$this->whereNulls[] = $field;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function whereNotNull(string $field): self
	{
		$this->whereNotNulls[] = $field;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function whereBetween(string $field, array $range): self
	{
		$this->whereBetweens[] = [
			'field' => $field,
			'range' => $range,
		];

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function orderBy(string $field, string $direction = 'asc'): self
	{
		$direction = strtolower($direction);
		$direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

		$this->orderBys[] = [
			'field' => $field,
			'direction' => $direction,
		];

		return $this;
	}
}
