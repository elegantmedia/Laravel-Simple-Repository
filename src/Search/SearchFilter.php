<?php


namespace ElegantMedia\SimpleRepository\Search;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use InvalidArgumentException;

/** @mixin Builder */
class SearchFilter implements Filterable
{

	protected $query;

	protected $perPage = 50;

	protected $isPaginated = true;

	public function __construct(Builder $query = null)
	{
		if ($query) {
			$this->setQuery($query);
		}
	}

	/**
	 *
	 * Set default options for the search filter
	 *
	 * @return $this
	 */
	public function setQueryDefaults(): self
	{
		if (!$this->query) {
			throw new InvalidArgumentException('You have not set a query first. Set a query with setQuery() method.');
		}

		// set defaults
		$request = request();

		if ($request->filled('q')) {
			$this->query->search($request->get('q'));
		}

		// default order
		$this->query->orderByDesc('id');

		return $this;
	}

	/**
	 * @param $query
	 *
	 * @return $this
	 */
	public function setQuery($query): self
	{
		$this->query = $query;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getPerPage(): int
	{
		return $this->perPage;
	}

	/**
	 * @param int $perPage
	 *
	 * @return SearchFilter
	 */
	public function setPerPage(int $perPage): self
	{
		$this->perPage = $perPage;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getQuery(): Builder
	{
		return $this->query;
	}

	/**
	 * @param bool $value
	 *
	 * @return $this
	 */
	public function paginate(bool $value): self
	{
		$this->isPaginated = $value;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isPaginated(): bool
	{
		return $this->isPaginated;
	}

	/**
	 * @param string $name
	 * @param $args
	 *
	 * @return mixed
	 */
	public function __call(string $name, $args)
	{
		if (!$this->query) {
			throw new QueryException('You have not set a query');
		}

		return call_user_func_array([$this->query, $name], $args);
	}

}
