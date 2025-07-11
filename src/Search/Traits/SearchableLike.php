<?php

declare(strict_types=1);

namespace ElegantMedia\SimpleRepository\Search\Traits;

use Illuminate\Database\Eloquent\Builder;

trait SearchableLike
{
	/**
	 * Fields that can be searched using LIKE queries.
	 *
	 * @var array<string>
	 */
	protected array $searchable = [];

	/**
	 * Search by keyword across searchable fields.
	 */
	public function scopeSearchByKeyword(Builder $query, ?string $keyword): Builder
	{
		if (empty($keyword) || empty($this->searchable)) {
			return $query;
		}

		return $query->where(function (Builder $query) use ($keyword) {
			$keyword = '%' . $keyword . '%';

			foreach ($this->searchable as $index => $field) {
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
		return $this->searchable;
	}

	/**
	 * Set searchable fields.
	 *
	 * @param array<string> $fields
	 */
	public function setSearchableFields(array $fields): self
	{
		$this->searchable = $fields;

		return $this;
	}
}
