<?php

declare(strict_types=1);

namespace Examples\App\Http\Controllers;

use Examples\App\Http\Requests\StoreCarRequest;
use Examples\App\Http\Requests\UpdateCarRequest;
use Examples\App\Http\Resources\CarResource;
use Examples\App\Models\CarsRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Cars API Controller.
 *
 * This controller provides a RESTful API for car data.
 * It demonstrates using the repository pattern for API endpoints
 * with larger pagination (100 items per page) and comprehensive filtering
 * through URL parameters.
 *
 * Example API calls:
 * GET /api/cars?search=Toyota&status=available&year_min=2020&price_max=50000&sort=price&order=asc&per_page=100
 * GET /api/cars/123
 * POST /api/cars
 * PUT /api/cars/123
 * DELETE /api/cars/123
 */
class CarsAPIController extends Controller
{
	/**
	 * Constructor with dependency injection.
	 */
	public function __construct(
		private readonly CarsRepository $carsRepository
	) {
	}

	/**
	 * Display a listing of cars via API.
	 *
	 * @OA\Get(
	 *     path="/api/cars",
	 *     summary="Get list of cars",
	 *     tags={"Cars"},
	 *
	 *     @OA\Parameter(name="search", in="query", description="Search keyword", @OA\Schema(type="string")),
	 *     @OA\Parameter(name="status", in="query", description="Filter by status", @OA\Schema(type="string", enum={"available", "sold", "reserved", "maintenance"})),
	 *     @OA\Parameter(name="make", in="query", description="Filter by make", @OA\Schema(type="string")),
	 *     @OA\Parameter(name="model", in="query", description="Filter by model", @OA\Schema(type="string")),
	 *     @OA\Parameter(name="year_min", in="query", description="Minimum year", @OA\Schema(type="integer")),
	 *     @OA\Parameter(name="year_max", in="query", description="Maximum year", @OA\Schema(type="integer")),
	 *     @OA\Parameter(name="price_min", in="query", description="Minimum price", @OA\Schema(type="number")),
	 *     @OA\Parameter(name="price_max", in="query", description="Maximum price", @OA\Schema(type="number")),
	 *     @OA\Parameter(name="color", in="query", description="Filter by color", @OA\Schema(type="string")),
	 *     @OA\Parameter(name="sort", in="query", description="Sort field", @OA\Schema(type="string", enum={"price", "year", "created_at", "make", "model"})),
	 *     @OA\Parameter(name="order", in="query", description="Sort order", @OA\Schema(type="string", enum={"asc", "desc"})),
	 *     @OA\Parameter(name="per_page", in="query", description="Items per page (max 100)", @OA\Schema(type="integer", maximum=100)),
	 *     @OA\Parameter(name="page", in="query", description="Page number", @OA\Schema(type="integer")),
	 *
	 *     @OA\Response(response=200, description="Success"),
	 *     @OA\Response(response=422, description="Validation error")
	 * )
	 *
	 * @param Request $request
	 *
	 * @return AnonymousResourceCollection
	 */
	public function index(Request $request): AnonymousResourceCollection
	{
		// Validate query parameters
		$validated = $request->validate([
			'search' => 'nullable|string|max:255',
			'status' => 'nullable|in:available,sold,reserved,maintenance',
			'make' => 'nullable|string|max:50',
			'model' => 'nullable|string|max:50',
			'year_min' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
			'year_max' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
			'price_min' => 'nullable|numeric|min:0',
			'price_max' => 'nullable|numeric|min:0',
			'color' => 'nullable|string|max:30',
			'sort' => 'nullable|in:price,year,created_at,make,model',
			'order' => 'nullable|in:asc,desc',
			'per_page' => 'nullable|integer|min:1|max:100',
		]);

		// Build filter with all possible parameters
		$filter = $this->carsRepository->newFilter();

		// Apply search keyword
		if (!empty($validated['search'])) {
			$filter->setKeyword($validated['search']);
		}

		// Apply filters
		if (!empty($validated['status'])) {
			$filter->where('status', $validated['status']);
		}

		if (!empty($validated['make'])) {
			$filter->where('make', 'LIKE', '%' . $validated['make'] . '%');
		}

		if (!empty($validated['model'])) {
			$filter->where('model', 'LIKE', '%' . $validated['model'] . '%');
		}

		if (!empty($validated['color'])) {
			$filter->where('color', 'LIKE', '%' . $validated['color'] . '%');
		}

		// Year range
		if (!empty($validated['year_min'])) {
			$filter->where('year', '>=', $validated['year_min']);
		}
		if (!empty($validated['year_max'])) {
			$filter->where('year', '<=', $validated['year_max']);
		}

		// Price range
		if (!empty($validated['price_min'])) {
			$filter->where('price', '>=', $validated['price_min']);
		}
		if (!empty($validated['price_max'])) {
			$filter->where('price', '<=', $validated['price_max']);
		}

		// Sorting
		$sortField = $validated['sort'] ?? 'created_at';
		$sortOrder = $validated['order'] ?? 'desc';
		$filter->setSortBy($sortField)->setSortDirection($sortOrder);

		// Pagination - default to 100 for API
		$perPage = $validated['per_page'] ?? 100;
		$filter->setPerPage($perPage);

		// Execute search
		$cars = $this->carsRepository->search($filter);

		// Return as API resource collection
		return CarResource::collection($cars)->additional([
			'meta' => [
				'available_filters' => [
					'status' => ['available', 'sold', 'reserved', 'maintenance'],
					'sort' => ['price', 'year', 'created_at', 'make', 'model'],
					'order' => ['asc', 'desc'],
				],
				'applied_filters' => array_filter($validated),
			],
		]);
	}

	/**
	 * Store a newly created car via API.
	 *
	 * @OA\Post(
	 *     path="/api/cars",
	 *     summary="Create a new car",
	 *     tags={"Cars"},
	 *
	 *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StoreCarRequest")),
	 *
	 *     @OA\Response(response=201, description="Car created successfully"),
	 *     @OA\Response(response=422, description="Validation error")
	 * )
	 *
	 * @param StoreCarRequest $request
	 *
	 * @return JsonResponse
	 */
	public function store(StoreCarRequest $request): JsonResponse
	{
		// Create car using repository with transaction
		$car = $this->carsRepository->transaction(function ($repository) use ($request) {
			return $repository->create($request->validated());
		});

		return response()->json([
			'message' => 'Car created successfully',
			'data' => new CarResource($car),
		], 201);
	}

	/**
	 * Display the specified car via API.
	 *
	 * @OA\Get(
	 *     path="/api/cars/{id}",
	 *     summary="Get car details",
	 *     tags={"Cars"},
	 *
	 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
	 *
	 *     @OA\Response(response=200, description="Success"),
	 *     @OA\Response(response=404, description="Car not found")
	 * )
	 *
	 * @param int $id
	 *
	 * @return CarResource
	 */
	public function show(int $id): CarResource
	{
		$car = $this->carsRepository->findOrFail($id);

		return new CarResource($car);
	}

	/**
	 * Update the specified car via API.
	 *
	 * @OA\Put(
	 *     path="/api/cars/{id}",
	 *     summary="Update a car",
	 *     tags={"Cars"},
	 *
	 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
	 *
	 *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdateCarRequest")),
	 *
	 *     @OA\Response(response=200, description="Car updated successfully"),
	 *     @OA\Response(response=404, description="Car not found"),
	 *     @OA\Response(response=422, description="Validation error")
	 * )
	 *
	 * @param UpdateCarRequest $request
	 * @param int              $id
	 *
	 * @return JsonResponse
	 */
	public function update(UpdateCarRequest $request, int $id): JsonResponse
	{
		$car = $this->carsRepository->findOrFail($id);

		// Update using repository with transaction
		$this->carsRepository->transaction(function ($repository) use ($car, $request) {
			$repository->updateModel($car, $request->validated());
		});

		return response()->json([
			'message' => 'Car updated successfully',
			'data' => new CarResource($car->fresh()),
		]);
	}

	/**
	 * Remove the specified car via API.
	 *
	 * @OA\Delete(
	 *     path="/api/cars/{id}",
	 *     summary="Delete a car",
	 *     tags={"Cars"},
	 *
	 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
	 *
	 *     @OA\Response(response=204, description="Car deleted successfully"),
	 *     @OA\Response(response=404, description="Car not found")
	 * )
	 *
	 * @param int $id
	 *
	 * @return JsonResponse
	 */
	public function destroy(int $id): JsonResponse
	{
		$this->carsRepository->delete($id);

		return response()->json(null, 204);
	}

	/**
	 * Get car statistics.
	 *
	 * @OA\Get(
	 *     path="/api/cars/stats",
	 *     summary="Get car statistics",
	 *     tags={"Cars"},
	 *
	 *     @OA\Response(response=200, description="Success")
	 * )
	 *
	 * @return JsonResponse
	 */
	public function stats(): JsonResponse
	{
		$stats = [
			'total_cars' => $this->carsRepository->count(),
			'available_cars' => $this->carsRepository->count(['status' => 'available']),
			'total_value' => $this->carsRepository->sum('price', ['status' => 'available']),
			'average_price' => $this->carsRepository->avg('price', ['status' => 'available']),
			'price_range' => [
				'min' => $this->carsRepository->min('price', ['status' => 'available']),
				'max' => $this->carsRepository->max('price', ['status' => 'available']),
			],
			'cars_by_status' => $this->carsRepository->getStatusCounts(),
			'popular_makes' => $this->carsRepository->getPopularMakes(5)->toArray(),
		];

		return response()->json([
			'data' => $stats,
		]);
	}

	/**
	 * Search cars with advanced options.
	 *
	 * @OA\Post(
	 *     path="/api/cars/search",
	 *     summary="Advanced car search",
	 *     tags={"Cars"},
	 *
	 *     @OA\RequestBody(
	 *         required=true,
	 *
	 *         @OA\JsonContent(
	 *
	 *             @OA\Property(property="filters", type="object"),
	 *             @OA\Property(property="sort", type="object"),
	 *             @OA\Property(property="pagination", type="object")
	 *         )
	 *     ),
	 *
	 *     @OA\Response(response=200, description="Success")
	 * )
	 *
	 * This endpoint allows for more complex search queries using POST
	 *
	 * @param Request $request
	 *
	 * @return AnonymousResourceCollection
	 */
	public function search(Request $request): AnonymousResourceCollection
	{
		$validated = $request->validate([
			'filters' => 'array',
			'filters.*.field' => 'required|string',
			'filters.*.operator' => 'required|in:=,!=,>,<,>=,<=,like,in,between',
			'filters.*.value' => 'required',
			'sort' => 'array',
			'sort.*.field' => 'required|string',
			'sort.*.direction' => 'required|in:asc,desc',
			'pagination' => 'array',
			'pagination.per_page' => 'integer|min:1|max:100',
			'pagination.page' => 'integer|min:1',
		]);

		$filter = $this->carsRepository->newFilter();

		// Apply complex filters
		if (!empty($validated['filters'])) {
			foreach ($validated['filters'] as $filterItem) {
				$field = $filterItem['field'];
				$operator = $filterItem['operator'];
				$value = $filterItem['value'];

				switch ($operator) {
					case 'like':
						$filter->where($field, 'LIKE', '%' . $value . '%');
						break;
					case 'in':
						$filter->whereIn($field, (array) $value);
						break;
					case 'between':
						if (is_array($value) && count($value) === 2) {
							$filter->whereBetween($field, $value);
						}
						break;
					default:
						$filter->where($field, $operator, $value);
				}
			}
		}

		// Apply multiple sorts
		if (!empty($validated['sort'])) {
			foreach ($validated['sort'] as $sortItem) {
				$filter->orderBy($sortItem['field'], $sortItem['direction']);
			}
		}

		// Apply pagination
		$perPage = $validated['pagination']['per_page'] ?? 100;
		$filter->setPerPage($perPage);

		$cars = $this->carsRepository->search($filter);

		return CarResource::collection($cars);
	}
}
