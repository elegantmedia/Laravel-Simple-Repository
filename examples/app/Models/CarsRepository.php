<?php

declare(strict_types=1);

namespace Examples\App\Models;

use ElegantMedia\SimpleRepository\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * Cars Repository.
 *
 * This repository handles all data access operations for the Car model.
 * It extends the BaseRepository to inherit all standard repository methods
 * while allowing for custom methods specific to cars.
 *
 * @method Car      newModel()
 * @method Car|null find(int|string $id, array $with = [])
 * @method Car      findOrFail(int|string $id)
 * @method Car      create(array $attributes)
 * @method Car      updateModel(Car $model, array $attributes)
 */
class CarsRepository extends BaseRepository
{
	/**
	 * Specify the model class name.
	 */
	protected function model(): string
	{
		return Car::class;
	}

	/**
	 * Find all available cars.
	 *
	 * @param array $with Relationships to eager load
	 *
	 * @return Collection<int, Car>
	 */
	public function findAvailable(array $with = []): Collection
	{
		return $this->newQuery()
			->with($with)
			->available()
			->get();
	}

	/**
	 * Find cars within a specific price range.
	 *
	 * @param float $minPrice Minimum price
	 * @param float $maxPrice Maximum price
	 * @param array $with     Relationships to eager load
	 *
	 * @return Collection<int, Car>
	 */
	public function findByPriceRange(float $minPrice, float $maxPrice, array $with = []): Collection
	{
		return $this->newQuery()
			->with($with)
			->priceRange($minPrice, $maxPrice)
			->orderBy('price', 'asc')
			->get();
	}

	/**
	 * Get cars grouped by status with counts.
	 *
	 * @return array<string, int>
	 */
	public function getStatusCounts(): array
	{
		return $this->newQuery()
			->selectRaw('status, COUNT(*) as count')
			->groupBy('status')
			->pluck('count', 'status')
			->toArray();
	}

	/**
	 * Find cars by make and model.
	 *
	 * @param string      $make  Car manufacturer
	 * @param string|null $model Car model (optional)
	 *
	 * @return Collection<int, Car>
	 */
	public function findByMakeAndModel(string $make, ?string $model = null): Collection
	{
		$query = $this->newQuery()->where('make', $make);

		if ($model !== null) {
			$query->where('model', $model);
		}

		return $query->orderBy('year', 'desc')->get();
	}

	/**
	 * Get popular car makes with counts.
	 *
	 * @param int $limit Number of top makes to return
	 *
	 * @return Collection
	 */
	public function getPopularMakes(int $limit = 10): Collection
	{
		return $this->newQuery()
			->selectRaw('make, COUNT(*) as count')
			->where('status', Car::STATUS_AVAILABLE)
			->groupBy('make')
			->orderByDesc('count')
			->limit($limit)
			->get();
	}

	/**
	 * Mark a car as sold.
	 *
	 * @param int|string $id Car ID
	 *
	 * @return bool
	 */
	public function markAsSold(int|string $id): bool
	{
		return $this->updateById($id, ['status' => Car::STATUS_SOLD]);
	}

	/**
	 * Reserve a car for a customer.
	 *
	 * @param int|string $id Car ID
	 *
	 * @return bool
	 */
	public function reserve(int|string $id): bool
	{
		return $this->updateById($id, ['status' => Car::STATUS_RESERVED]);
	}

	/**
	 * Get cars for admin dashboard with advanced filtering.
	 *
	 * This method demonstrates using the filter builder with custom logic
	 *
	 * @param array $filters Array of filter criteria
	 *
	 * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
	 */
	public function getForAdminDashboard(array $filters = [])
	{
		$filter = $this->newFilter();

		// Apply search keyword if provided
		if (!empty($filters['search'])) {
			$filter->setKeyword($filters['search']);
		}

		// Filter by status
		if (!empty($filters['status'])) {
			$filter->where('status', $filters['status']);
		}

		// Filter by year range
		if (!empty($filters['year_from'])) {
			$filter->where('year', '>=', $filters['year_from']);
		}
		if (!empty($filters['year_to'])) {
			$filter->where('year', '<=', $filters['year_to']);
		}

		// Filter by price range
		if (!empty($filters['price_min'])) {
			$filter->where('price', '>=', $filters['price_min']);
		}
		if (!empty($filters['price_max'])) {
			$filter->where('price', '<=', $filters['price_max']);
		}

		// Apply sorting
		$sortBy = $filters['sort_by'] ?? 'created_at';
		$sortDirection = $filters['sort_direction'] ?? 'desc';
		$filter->setSortBy($sortBy)->setSortDirection($sortDirection);

		// Set pagination
		$perPage = $filters['per_page'] ?? 50;
		$filter->setPerPage($perPage);

		return $this->search($filter);
	}
}
