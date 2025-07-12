<?php

declare(strict_types=1);

namespace Examples\App\Http\Controllers;

use Examples\App\Models\CarsRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Cars Controller - Public Frontend.
 *
 * This controller handles the public-facing car listings and details.
 * It demonstrates using the repository pattern for a customer-facing website
 * with moderate pagination (20 items per page) and basic filtering.
 */
class CarsController extends Controller
{
	/**
	 * Constructor with dependency injection of the repository.
	 */
	public function __construct(
		private readonly CarsRepository $carsRepository
	) {
	}

	/**
	 * Display a listing of available cars for public viewing.
	 *
	 * Features:
	 * - Shows only available cars
	 * - 20 items per page for comfortable browsing
	 * - Basic search functionality
	 * - Price range filtering
	 *
	 * @param Request $request
	 *
	 * @return View
	 */
	public function index(Request $request): View
	{
		// Create a filter for public car listings
		$filter = $this->carsRepository->newFilter()
			->where('status', 'available') // Only show available cars to public
			->setSortBy('created_at')
			->setSortDirection('desc')
			->setPaginate(true)
			->setPerPage(20); // 20 items per page for public viewing

		// Apply search if provided
		if ($request->filled('search')) {
			$filter->setKeyword($request->get('search'));
		}

		// Apply price range filter if provided
		if ($request->filled('price_min')) {
			$filter->where('price', '>=', $request->get('price_min'));
		}
		if ($request->filled('price_max')) {
			$filter->where('price', '<=', $request->get('price_max'));
		}

		// Apply make filter if provided
		if ($request->filled('make')) {
			$filter->where('make', $request->get('make'));
		}

		// Apply year filter if provided
		if ($request->filled('year')) {
			$filter->where('year', $request->get('year'));
		}

		// Get paginated results
		$cars = $this->carsRepository->search($filter);

		// Get popular makes for filter dropdown
		$popularMakes = $this->carsRepository->getPopularMakes(15);

		return view('cars.index', compact('cars', 'popularMakes'));
	}

	/**
	 * Display the specified car details.
	 *
	 * @param int $id
	 *
	 * @return View
	 */
	public function show(int $id): View
	{
		// Find the car or throw 404
		$car = $this->carsRepository->findOrFail($id);

		// Only show if available (additional security check)
		if ($car->status !== 'available') {
			abort(404, 'This car is no longer available.');
		}

		// Find similar cars (same make, different model)
		$similarCars = $this->carsRepository->newFilter()
			->where('make', $car->make)
			->where('id', '!=', $car->id)
			->where('status', 'available')
			->setPerPage(4)
			->setPaginate(false); // No pagination for similar cars

		$similarCars = $this->carsRepository->search($similarCars);

		return view('cars.show', compact('car', 'similarCars'));
	}

	/**
	 * Show cars by make.
	 *
	 * @param string $make
	 *
	 * @return View
	 */
	public function byMake(string $make): View
	{
		// Create filter for specific make
		$filter = $this->carsRepository->newFilter()
			->where('make', $make)
			->where('status', 'available')
			->setSortBy('price')
			->setSortDirection('asc')
			->setPerPage(20);

		$cars = $this->carsRepository->search($filter);

		return view('cars.by-make', compact('cars', 'make'));
	}

	/**
	 * Show featured cars for homepage.
	 *
	 * @return View
	 */
	public function featured(): View
	{
		// Get recently added available cars
		$filter = $this->carsRepository->newFilter()
			->where('status', 'available')
			->whereDateLastDays(7) // Cars added in last 7 days
			->setSortBy('created_at')
			->setSortDirection('desc')
			->setPerPage(8)
			->setPaginate(false); // No pagination for featured section

		$featuredCars = $this->carsRepository->search($filter);

		return view('components.featured-cars', compact('featuredCars'));
	}

	/**
	 * Handle car inquiry/contact form.
	 *
	 * @param Request $request
	 * @param int     $id
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function inquiry(Request $request, int $id)
	{
		$validated = $request->validate([
			'name' => 'required|string|max:255',
			'email' => 'required|email',
			'phone' => 'required|string|max:20',
			'message' => 'required|string|max:1000',
		]);

		$car = $this->carsRepository->findOrFail($id);

		// Here you would typically send an email or save the inquiry
		// For this example, we'll just flash a success message

		return redirect()
			->route('cars.show', $car->id)
			->with('success', 'Thank you for your inquiry! We will contact you soon.');
	}
}
