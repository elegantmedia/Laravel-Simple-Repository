<?php


namespace ElegantMedia\SimpleRepository;

interface SimpleRepositoryInterface
{

	public function newModel();

	public function newQuery();

	/*
	 |-----------------------------------------------------------
	 | List/Index/Search
	 |-----------------------------------------------------------
	 */

	public function all($with = []);

	public function paginate($perPage = 50, $with = [], $filter = null);

	public function simplePaginate($perPage = 50, $with = [], $filter = null);

	public function search($filter = null);

	/*
	 |-----------------------------------------------------------
	 | Find
	 |-----------------------------------------------------------
	 */

	public function find($id, $with = []);

	public function findByUuid($uuid, $with = []);

	public function findByField($field, $value, $with = []);

	public function findOrCreate(array $attributes, $id = 'id');

	public function findOrFail($id);

	public function findByAttribute($attributes, $whereValue, $whereKey = 'id');

	/*
	 |-----------------------------------------------------------
	 | Create
	 |-----------------------------------------------------------
	 */

	public function create($attributes);

	/*
	 |-----------------------------------------------------------
	 | Update or Insert/Create
	 |-----------------------------------------------------------
	 */

	public function updateOrInsert($attributes, $idColumn = 'id');

	public function updateOrInsertByUuid($attributes);

	/*
	 |-----------------------------------------------------------
	 | Save/Update
	 |-----------------------------------------------------------
	 */

	public function update($model, array $attributes);

	public function updateById(array $attributes, $id, $idColumn = 'id');

	public function save($model);

	/*
	 |-----------------------------------------------------------
	 | Destroy
	 |-----------------------------------------------------------
	 */

	public function delete($id);

	public function deleteWhere(array $where);
}
