<?php


namespace ElegantMedia\SimpleRepository;

use ElegantMedia\SimpleRepositoriy\Exceptions\KeyNotFoundInAttributesException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Builder;

class SimpleBaseRepository implements SimpleRepositoryInterface
{

	protected $primaryKey = 'id';

	/**
	 * @var Model|null
	 */
	protected $model;

	public function __construct(Model $model = null)
	{
		$this->model = $model;
	}

	public function newModel()
	{
		$this->validatePrerequisites();

		$class = get_class($this->model);

		return new $class;
	}

	public function newQuery($columns = ['*'])
	{
		$this->validatePrerequisites();

		return $this->model->select($columns);
	}

	protected function addQueryWithClauses(Builder $query, array $with = [])
	{
		foreach ($with as $relation) {
			$query->with($relation);
		}
	}

	/*
	|--------------------------------------------------------------------------
	| List/Index/Search
	|--------------------------------------------------------------------------
	|
	|
	|
	*/

	public function all($with = [])
	{
		$query = $this->newQuery();

		$this->addQueryWithClauses($query, $with);

		return $query->get();
	}

	public function paginate($perPage = 50, $with = [], $filter = null)
	{
		$query = $this->newQuery();

		$this->addQueryWithClauses($query, $with);

		return $query->paginate($perPage);
	}

	public function simplePaginate($perPage = 50, $with = [], $filter = null)
	{
		$query = $this->newQuery();

		$this->addQueryWithClauses($query, $with);

		return $query->simplePaginate($perPage);
	}

	public function search($filter = null)
	{
		$model = $this->getModel();

		$searchQuery = request()->get('q');

		return $model::search($searchQuery);
	}

	public function searchPaginate($filter = null)
	{
		return $this->search($filter)->paginate();
	}

	/*
	|--------------------------------------------------------------------------
	| Find
	|--------------------------------------------------------------------------
	|
	|
	|
	*/

	public function find($id, $with = [])
	{
		$query = $this->newQuery();

		$this->addQueryWithClauses($query, $with);

		return $query->find($id);
	}

	public function findByUuid($uuid, $with = [])
	{
		return $this->findByField('uuid', $uuid, $with);
	}

	public function findByField($field, $value, $with = [])
	{
		$query = $this->newQuery();

		$this->addQueryWithClauses($query, $with);

		return $query->where($field, '=', $value)->first();
	}

	public function findOrCreate(array $attributes, $id = null)
	{
		$id = $this->getPrimaryKey($id);

		if (isset($attributes[$id])) {
			$model = $this->findByField($id, $attributes[$id]);

			if ($model) {
				return $model;
			}
		}

		return $this->create($attributes);
	}

	public function findOrFail($id)
	{
		return $this->newQuery()->findOrFail($id);
	}

	public function findByAttribute($attributes, $whereValue, $whereKey = null)
	{
		$whereKey = $this->getPrimaryKey($whereKey);

		try {
			$this->validateAttributesHaveKey($attributes, $whereKey);
		} catch (KeyNotFoundInAttributesException $e) {
			return null;
		}

		return $this->findByField($attributes[$whereKey], $whereValue);
	}



	/*
	|--------------------------------------------------------------------------
	| Create
	|--------------------------------------------------------------------------
	|
	|
	|
	*/

	public function create($attributes)
	{
		$model = $this->newModel();

		$model->fill($attributes)->save();

		return $model;
	}


	/*
	|--------------------------------------------------------------------------
	| Update or Insert/Create
	|--------------------------------------------------------------------------
	|
	|
	|
	*/


	public function updateOrInsert($attributes, $id = null)
	{
		$id = $this->getPrimaryKey($id);

		if (isset($attributes[$id])) {
			$model = $this->findByField($id, $attributes[$id]);

			if ($model) {
				// remove the ID, because we already have the correct one
				unset($attributes[$id]);
				return $this->update($model, $attributes);
			}
		}

		return $this->create($attributes);
	}

	public function updateOrInsertByUuid($attributes)
	{
		return $this->updateOrInsert($attributes, 'uuid');
	}


	/*
	|--------------------------------------------------------------------------
	| Save/Update
	|--------------------------------------------------------------------------
	|
	|
	|
	*/

	/**
	 * @param $model
	 * @param array $attributes
	 * @return mixed
	 */
	public function update($model, array $attributes)
	{
		return $model->update($attributes);
	}

	/**
	 *
	 * Update a Model by a given ID
	 *
	 * @example
	 * If attributes contain ['id' => 5, 'name' => 'John'],
	 * it will find the record and update it.
	 *
	 * @param array $attributes
	 * @param $id
	 * @param string|null $idColumn
	 * @throws KeyNotFoundInAttributesException
	 * @throws ModelNotFoundException
	 * @return Model
	 */
	public function updateById(array $attributes, $id, $idColumn = null)
	{
		$idColumn = $this->getPrimaryKey($idColumn);

		$this->validateAttributesHaveKey($attributes, $idColumn);

		$model = $this->findByField($idColumn, $id);

		if (!$model) {
			throw new ModelNotFoundException("Model not found with `{$idColumn}` of {$id}");
		}

		// unset the key
		unset($attributes[$idColumn]);

		return $this->update($model, $attributes);
	}

	public function save($model)
	{
		return $model->save();
	}

	public function delete($ids)
	{
		return $this->getModel()->destroy($ids);
	}

	public function deleteWhere(array $where)
	{
		$query = $this->newQuery();

		$query->where($where);

		return $query->get()->delete();
	}

	/*
	|--------------------------------------------------------------------------
	| Getters and Setters
	|--------------------------------------------------------------------------
	|
	|
	|
	*/

	/**
	 * @param Model|null $model
	 * @return SimpleBaseRepository
	 */
	public function setModel(?Model $model): SimpleBaseRepository
	{
		$this->model = $model;
		return $this;
	}

	/**
	 * @return Model|null
	 */
	public function getModel(): ?Model
	{
		return $this->model;
	}

	/**
	 * @param string $primaryKey
	 * @return SimpleBaseRepository
	 */
	public function setPrimaryKey(string $primaryKey): SimpleBaseRepository
	{
		$this->primaryKey = $primaryKey;
		return $this;
	}

	/**
	 * @param null $key
	 * @return string
	 */
	public function getPrimaryKey($key = null): string
	{
		if ($key) {
			return $key;
		}

		return $this->primaryKey;
	}


	/*
	|--------------------------------------------------------------------------
	| Validators
	|--------------------------------------------------------------------------
	|
	|
	|
	*/

	/**
	 * @return bool
	 */
	protected function validatePrerequisites(): bool
	{
		if ($this->model === null) {
			throw new ModelNotFoundException(
				'Model not set on repository. Pass a model to constructor or call `setModel()` to set a model.'
			);
		}

		return true;
	}

	/**
	 * @param $attributes
	 * @param $key
	 * @throws KeyNotFoundInAttributesException
	 */
	protected function validateAttributesHaveKey($attributes, $key): void
	{
		if (!isset($attributes[$key])) {
			throw new KeyNotFoundInAttributesException();
		}
	}


}
