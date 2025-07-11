<?php

declare(strict_types=1);

namespace ElegantMedia\SimpleRepository\Search\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface FilterableInterface
{
	/**
	 * Apply filters to the query builder.
	 */
	public function apply(Builder $query): Builder;

	/**
	 * Set whether to paginate results.
	 */
	public function paginate(bool $paginate = true): self;

	/**
	 * Check if results should be paginated.
	 */
	public function shouldPaginate(): bool;

	/**
	 * Set the number of results per page.
	 */
	public function setPerPage(int $perPage): self;

	/**
	 * Get the number of results per page.
	 */
	public function getPerPage(): int;

	/**
	 * Set the search keyword.
	 */
	public function setKeyword(?string $keyword): self;

	/**
	 * Get the search keyword.
	 */
	public function getKeyword(): ?string;

	/**
	 * Set the sort field.
	 */
	public function setSortBy(string $field): self;

	/**
	 * Get the sort field.
	 */
	public function getSortBy(): string;

	/**
	 * Set the sort direction.
	 */
	public function setSortDirection(string $direction): self;

	/**
	 * Get the sort direction.
	 */
	public function getSortDirection(): string;

	/**
	 * Add relationships to eager load.
	 *
	 * @param array<string>|string $relations
	 */
	public function with(array|string $relations): self;

	/**
	 * Add a where condition.
	 */
	public function where(string $field, mixed $operator, mixed $value = null): self;

	/**
	 * Add a whereHas condition.
	 */
	public function whereHas(string $relation, callable $callback): self;

	/**
	 * Set the search term (alias for setKeyword).
	 */
	public function setSearchBy(string $term): self;

	/**
	 * Set the sort order (alias for setSortDirection).
	 */
	public function setSortOrder(string $direction): self;

	/**
	 * Add a where in condition.
	 *
	 * @param array<mixed> $values
	 */
	public function whereIn(string $field, array $values): self;

	/**
	 * Add a where null condition.
	 */
	public function whereNull(string $field): self;

	/**
	 * Add a where not null condition.
	 */
	public function whereNotNull(string $field): self;

	/**
	 * Add a where between condition.
	 *
	 * @param array{0: mixed, 1: mixed} $range
	 */
	public function whereBetween(string $field, array $range): self;

	/**
	 * Add an order by clause.
	 */
	public function orderBy(string $field, string $direction = 'asc'): self;

	/**
	 * Get the results.
	 *
	 * @return LengthAwarePaginator|Collection<int, \Illuminate\Database\Eloquent\Model>
	 */
	public function get(): LengthAwarePaginator|Collection;
}
