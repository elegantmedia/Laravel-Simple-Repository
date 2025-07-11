<?php

declare(strict_types=1);

namespace ElegantMedia\SimpleRepository\Search\Filters;

use ElegantMedia\SimpleRepository\Search\Contracts\DateFilterInterface;
use ElegantMedia\SimpleRepository\Search\Contracts\FilterableInterface;
use ElegantMedia\SimpleRepository\Search\Contracts\FinancialDateFilterInterface;
use ElegantMedia\SimpleRepository\Search\Traits\DateFilterableTrait;
use ElegantMedia\SimpleRepository\Search\Traits\FinancialDateFilterableTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class SearchFilter implements FilterableInterface, DateFilterInterface, FinancialDateFilterInterface
{
	use DateFilterableTrait;
	use FinancialDateFilterableTrait;

	protected Builder $query;

	protected bool $shouldPaginate = true;

	/** @var array<array{type: string, args: array}> */
	protected array $conditions = [];

	protected int $perPage = 50;

	protected ?string $keyword = null;

	protected string $sortBy = 'created_at';

	protected string $sortDirection = 'desc';

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
		// Apply all stored conditions
		foreach ($this->conditions as $condition) {
			$method = $condition['type'];
			$args = $condition['args'];
			$query->{$method}(...$args);
		}

		// Apply search keyword if model is searchable
		if ($this->keyword !== null && method_exists($query->getModel(), 'scopeSearchByKeyword')) {
			$query->searchByKeyword($this->keyword);
		}

		// Apply default sorting if no orderBy has been set
		if (empty($query->getQuery()->orders) && empty($this->conditions)) {
			$query->orderBy($this->sortBy, $this->sortDirection);
		}

		return $query;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setPaginate(bool $paginate = true): self
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
	 * Get the results of the filter.
	 *
	 * @return LengthAwarePaginator|Collection
	 */
	public function get(): LengthAwarePaginator|Collection
	{
		// Apply all conditions to the internal query
		foreach ($this->conditions as $condition) {
			$method = $condition['type'];
			$args = $condition['args'];

			// Special handling for 'with' method when second argument is null
			if ($method === 'with' && count($args) === 2 && $args[1] === null) {
				$this->query->with($args[0]);
			} else {
				$this->query->{$method}(...$args);
			}
		}

		// Apply search keyword if model is searchable
		if ($this->keyword !== null && method_exists($this->query->getModel(), 'scopeSearchByKeyword')) {
			$this->query->searchByKeyword($this->keyword);
		}

		// Apply default sorting if no orderBy has been set
		if (empty($this->query->getQuery()->orders) && !$this->hasOrderByCondition()) {
			$this->query->orderBy($this->sortBy, $this->sortDirection);
		}

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
	 * Add a where condition.
	 */
	public function where($column, $operator = null, $value = null, $boolean = 'and'): self
	{
		// Store the condition to apply later
		if (func_num_args() === 2) {
			$value = $operator;
			$operator = '=';
		}

		$this->conditions[] = [
			'type' => 'where',
			'args' => [$column, $operator, $value, $boolean],
		];

		return $this;
	}

	/**
	 * Add a whereIn condition.
	 */
	public function whereIn($column, $values, $boolean = 'and', $not = false): self
	{
		$this->conditions[] = [
			'type' => 'whereIn',
			'args' => [$column, $values, $boolean, $not],
		];

		return $this;
	}

	/**
	 * Add a whereNull condition.
	 */
	public function whereNull($columns, $boolean = 'and', $not = false): self
	{
		$this->conditions[] = [
			'type' => 'whereNull',
			'args' => [$columns, $boolean, $not],
		];

		return $this;
	}

	/**
	 * Add a whereNotNull condition.
	 */
	public function whereNotNull($columns, $boolean = 'and'): self
	{
		$this->conditions[] = [
			'type' => 'whereNotNull',
			'args' => [$columns, $boolean],
		];

		return $this;
	}

	/**
	 * Add a whereBetween condition.
	 */
	public function whereBetween($column, iterable $values, $boolean = 'and', $not = false): self
	{
		$this->conditions[] = [
			'type' => 'whereBetween',
			'args' => [$column, $values, $boolean, $not],
		];

		return $this;
	}

	/**
	 * Add a whereHas condition.
	 */
	public function whereHas($relation, \Closure $callback = null, $operator = '>=', $count = 1): self
	{
		$this->conditions[] = [
			'type' => 'whereHas',
			'args' => [$relation, $callback, $operator, $count],
		];

		return $this;
	}

	/**
	 * Add an orderBy clause.
	 */
	public function orderBy($column, $direction = 'asc'): self
	{
		$this->conditions[] = [
			'type' => 'orderBy',
			'args' => [$column, $direction],
		];

		return $this;
	}

	/**
	 * Add relationships to eager load.
	 */
	public function with($relations, $callback = null): self
	{
		$this->conditions[] = [
			'type' => 'with',
			'args' => [$relations, $callback],
		];

		return $this;
	}

	/**
	 * Check if any orderBy condition has been added.
	 */
	protected function hasOrderByCondition(): bool
	{
		foreach ($this->conditions as $condition) {
			if ($condition['type'] === 'orderBy') {
				return true;
			}
		}

		return false;
	}

	/**
	 * Proxy to other Laravel Builder methods.
	 */
	public function __call($method, $parameters)
	{
		$this->conditions[] = [
			'type' => $method,
			'args' => $parameters,
		];

		return $this;
	}
}
