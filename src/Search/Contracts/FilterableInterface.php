<?php

declare(strict_types=1);

namespace ElegantMedia\SimpleRepository\Search\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface FilterableInterface
{
	/**
	 * Apply filters to the query builder.
	 */
	public function apply(Builder $query): Builder;

	/**
	 * Set whether to paginate results.
	 */
	public function setPaginate(bool $paginate = true): self;

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
	 * Set the search term (alias for setKeyword).
	 */
	public function setSearchBy(string $term): self;

	/**
	 * Set the sort order (alias for setSortDirection).
	 */
	public function setSortOrder(string $direction): self;
}
