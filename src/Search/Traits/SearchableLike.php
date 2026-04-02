<?php

declare(strict_types=1);

namespace ElegantMedia\SimpleRepository\Search\Traits;

use Illuminate\Database\Eloquent\Builder;

trait SearchableLike
{
	/**
	 * Override fields that can be searched using LIKE queries.
	 *
	 * This exists so consumers can call `setSearchableFields()` at runtime without
	 * forcing a `$searchable` property onto the consuming model (which would
	 * conflict if the model already defines it).
	 *
	 * @var array<string>|null
	 */
	protected ?array $searchableOverride = null;

	/**
	 * Search by keyword across searchable fields.
	 */
	public function scopeSearchByKeyword(Builder $query, ?string $keyword): Builder
	{
		$searchableFields = $this->getSearchableFields();

		if (empty($keyword) || empty($searchableFields)) {
			return $query;
		}

		return $query->where(function (Builder $query) use ($keyword, $searchableFields) {
			$keyword = '%' . $keyword . '%';

			foreach ($searchableFields as $index => $field) {
				if ($index === 0) {
					$query->where($field, 'LIKE', $keyword);
				} else {
					$query->orWhere($field, 'LIKE', $keyword);
				}
			}
		});
	}

	/**
	 * Get searchable fields.
	 *
	 * @return array<string>
	 */
	public function getSearchableFields(): array
	{
		if ($this->searchableOverride !== null) {
			return $this->searchableOverride;
		}

		// Allow consuming models to define their own `$searchable` property without
		// trait property conflicts.
		if (property_exists($this, 'searchable')) {
			/** @var mixed $fields */
			$fields = $this->searchable;

			if (is_array($fields)) {
				// Best-effort normalization: ensure we return array<string>.
				return array_values(array_filter($fields, static fn ($value): bool => is_string($value) && $value !== ''));
			}
		}

		return [];
	}

	/**
	 * Set searchable fields.
	 *
	 * @param array<string> $fields
	 */
	public function setSearchableFields(array $fields): self
	{
		$this->searchableOverride = $fields;

		return $this;
	}
}
