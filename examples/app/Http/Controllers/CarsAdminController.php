<?php

declare(strict_types=1);

namespace Examples\App\Http\Controllers;

use Examples\App\Http\Requests\StoreCarRequest;
use Examples\App\Http\Requests\UpdateCarRequest;
use Examples\App\Models\Car;
use Examples\App\Models\CarsRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Cars Admin Controller.
 *
 * This controller handles car management in the admin dashboard.
 * It demonstrates using the repository pattern for administrative tasks
 * with larger pagination (50 items per page) and comprehensive CRUD operations.
 */
class CarsAdminController extends Controller
{
	/**
	 * Constructor with dependency injection.
	 */
	public function __construct(
		private readonly CarsRepository $carsRepository
	) {
	}

	/**
	 * Display a listing of all cars in admin dashboard.
	 *
	 * Features:
	 * - Shows all cars regardless of status
	 * - 50 items per page for efficient management
	 * - Advanced filtering options
	 * - Includes soft-deleted records option
	 *
	 * @param Request $request
	 *
	 * @return View
	 */
	public function index(Request $request): View
	{
		// Build filter from request parameters
		$filters = $request->only([
			'search',
			'status',
			'year_from',
			'year_to',
			'price_min',
			'price_max',
			'sort_by',
			'sort_direction',
		]);

		// Set default per_page for admin
		$filters['per_page'] = 50;

		// Get paginated results using the repository method
		$cars = $this->carsRepository->getForAdminDashboard($filters);

		// Get status counts for dashboard widgets
		$statusCounts = $this->carsRepository->getStatusCounts();

		// Get total value of inventory
		$totalValue = $this->carsRepository->sum('price', ['status' => Car::STATUS_AVAILABLE]);

		return view('admin.cars.index', compact('cars', 'statusCounts', 'totalValue'));
	}

	/**
	 * Show the form for creating a new car.
	 *
	 * @return View
	 */
	public function create(): View
	{
		$statuses = Car::getStatuses();

		return view('admin.cars.create', compact('statuses'));
	}

	/**
	 * Store a newly created car in storage.
	 *
	 * @param StoreCarRequest $request
	 *
	 * @return RedirectResponse
	 */
	public function store(StoreCarRequest $request): RedirectResponse
	{
		// Use transaction for data integrity
		$car = $this->carsRepository->transaction(function ($repository) use ($request) {
			// Create the car
			$car = $repository->create($request->validated());

			// Log the action (in a real app, you'd have an audit log)
			activity()
				->performedOn($car)
				->log('Car created via admin panel');

			return $car;
		});

		return redirect()
			->route('admin.cars.show', $car->id)
			->with('success', 'Car has been successfully added to inventory.');
	}

	/**
	 * Display the specified car details in admin view.
	 *
	 * @param int $id
	 *
	 * @return View
	 */
	public function show(int $id): View
	{
		// Include soft-deleted records for admin
		$car = $this->carsRepository->findWithTrashed($id);

		if (!$car) {
			abort(404);
		}

		// Get car history/audit log (simplified example)
		$history = [
			'created' => $car->created_at,
			'last_updated' => $car->updated_at,
			'deleted' => $car->deleted_at,
		];

		return view('admin.cars.show', compact('car', 'history'));
	}

	/**
	 * Show the form for editing the specified car.
	 *
	 * @param int $id
	 *
	 * @return View
	 */
	public function edit(int $id): View
	{
		$car = $this->carsRepository->findOrFail($id);
		$statuses = Car::getStatuses();

		return view('admin.cars.edit', compact('car', 'statuses'));
	}

	/**
	 * Update the specified car in storage.
	 *
	 * @param UpdateCarRequest $request
	 * @param int              $id
	 *
	 * @return RedirectResponse
	 */
	public function update(UpdateCarRequest $request, int $id): RedirectResponse
	{
		$car = $this->carsRepository->findOrFail($id);

		// Update with transaction
		$this->carsRepository->transaction(function ($repository) use ($car, $request) {
			$repository->updateModel($car, $request->validated());

			// Log the update
			activity()
				->performedOn($car)
				->withProperties(['old' => $car->getOriginal(), 'new' => $car->getAttributes()])
				->log('Car updated via admin panel');
		});

		return redirect()
			->route('admin.cars.show', $car->id)
			->with('success', 'Car has been successfully updated.');
	}

	/**
	 * Remove the specified car from storage (soft delete).
	 *
	 * @param int $id
	 *
	 * @return RedirectResponse
	 */
	public function destroy(int $id): RedirectResponse
	{
		$car = $this->carsRepository->findOrFail($id);

		// Soft delete the car
		$this->carsRepository->delete($id);

		return redirect()
			->route('admin.cars.index')
			->with('success', 'Car has been moved to trash.');
	}

	/**
	 * Restore a soft-deleted car.
	 *
	 * @param int $id
	 *
	 * @return RedirectResponse
	 */
	public function restore(int $id): RedirectResponse
	{
		$this->carsRepository->restore($id);

		return redirect()
			->route('admin.cars.show', $id)
			->with('success', 'Car has been restored from trash.');
	}

	/**
	 * Permanently delete a car.
	 *
	 * @param int $id
	 *
	 * @return RedirectResponse
	 */
	public function forceDelete(int $id): RedirectResponse
	{
		// Extra confirmation would be needed in a real app
		$this->carsRepository->forceDelete($id);

		return redirect()
			->route('admin.cars.index')
			->with('success', 'Car has been permanently deleted.');
	}

	/**
	 * Bulk actions handler.
	 *
	 * @param Request $request
	 *
	 * @return RedirectResponse
	 */
	public function bulkAction(Request $request): RedirectResponse
	{
		$validated = $request->validate([
			'action' => 'required|in:delete,restore,update_status',
			'car_ids' => 'required|array',
			'car_ids.*' => 'integer|exists:cars,id',
			'status' => 'required_if:action,update_status|in:available,sold,reserved,maintenance',
		]);

		$carIds = $validated['car_ids'];
		$action = $validated['action'];

		// Use transaction for bulk operations
		$this->carsRepository->transaction(function ($repository) use ($action, $carIds, $validated) {
			switch ($action) {
				case 'delete':
					$repository->deleteManyByIds($carIds);
					break;

				case 'restore':
					foreach ($carIds as $id) {
						$repository->restore($id);
					}
					break;

				case 'update_status':
					$repository->updateWhere(
						['id' => $carIds],
						['status' => $validated['status']]
					);
					break;
			}
		});

		return redirect()
			->route('admin.cars.index')
			->with('success', 'Bulk action completed successfully.');
	}

	/**
	 * Export cars data.
	 *
	 * @param Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\StreamedResponse
	 */
	public function export(Request $request)
	{
		// Apply same filters as index
		$filters = $request->only(['search', 'status', 'year_from', 'year_to', 'price_min', 'price_max']);

		// Disable pagination for export
		$filter = $this->carsRepository->newFilter()->setPaginate(false);

		// Apply filters...
		if (!empty($filters['search'])) {
			$filter->setKeyword($filters['search']);
		}

		$cars = $this->carsRepository->search($filter);

		// In a real app, you'd use a proper CSV/Excel export package
		return response()->streamDownload(function () use ($cars) {
			$handle = fopen('php://output', 'w');

			// Headers
			fputcsv($handle, ['ID', 'Make', 'Model', 'Year', 'Price', 'Status', 'VIN', 'Created']);

			// Data
			foreach ($cars as $car) {
				fputcsv($handle, [
					$car->id,
					$car->make,
					$car->model,
					$car->year,
					$car->price,
					$car->status,
					$car->vin,
					$car->created_at->format('Y-m-d H:i:s'),
				]);
			}

			fclose($handle);
		}, 'cars-export-' . date('Y-m-d') . '.csv');
	}
}
