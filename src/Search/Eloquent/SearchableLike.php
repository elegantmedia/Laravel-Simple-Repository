<?php


namespace ElegantMedia\SimpleRepository\Search\Eloquent;

trait SearchableLike
{

	/**
	 *
	 * Searchable method
	 *
	 * @return array
	 */
	public function searchable(): array
	{
		if (isset($this->searchable)) {
			return $this->searchable;
		}

		return [];
	}

	/**
	 *
	 * Add a basic LIKE based searchable feature
	 *
	 * @param $query
	 * @param $searchQuery
	 */
	public function scopeSearch($query, $searchQuery): void
	{
		$query->where(function ($query) use ($searchQuery) {
			foreach ($this->searchable() as $searchField) {
				$query->orWhere($searchField, 'LIKE', '%' . $searchQuery . '%');
			}
		});
	}
}
