<?php


namespace ElegantMedia\SimpleRepository\Search;


use Illuminate\Database\Eloquent\Builder;

interface Filterable
{

	public function getQuery(): Builder;

	public function getPerPage(): int;

	public function paginate(bool $value);

	public function isPaginated(): bool;

}
