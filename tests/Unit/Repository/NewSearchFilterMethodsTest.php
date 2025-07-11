<?php

namespace ElegantMedia\SimpleRepository\Tests\Unit\Repository;

use ElegantMedia\SimpleRepository\Search\Filters\SearchFilter;
use ElegantMedia\SimpleRepository\Tests\Fixtures\Models\TestModel;
use ElegantMedia\SimpleRepository\Tests\Fixtures\Repositories\TestRepository;
use ElegantMedia\SimpleRepository\Tests\TestCase;
use Illuminate\Support\Carbon;

class NewSearchFilterMethodsTest extends TestCase
{
	private TestRepository $repository;

	protected function setUp(): void
	{
		parent::setUp();

		$this->repository = new TestRepository();
	}

	/**
	 * Test setSearchBy method (alias for setKeyword).
	 */
	public function test_set_search_by_sets_search_term(): void
	{
		$query = TestModel::query();
		$filter = new SearchFilter($query);
		$filter->setSearchBy('test keyword');

		// Verify the search term is set
		$this->assertEquals('test keyword', $filter->getKeyword());
	}

	public function test_set_search_by_is_chainable(): void
	{
		$filter = new SearchFilter(TestModel::query());

		$result = $filter->setSearchBy('chainable');

		$this->assertInstanceOf(SearchFilter::class, $result);
		$this->assertSame($filter, $result);
	}

	/**
	 * Test setSortOrder method (alias for setSortDirection).
	 */
	public function test_set_sort_order_sets_direction(): void
	{
		$filter = new SearchFilter(TestModel::query());

		$filter->setSortBy('name');
		$filter->setSortOrder('desc');

		$this->assertEquals('desc', $filter->getSortDirection());
	}

	public function test_set_sort_order_accepts_uppercase(): void
	{
		$filter = new SearchFilter(TestModel::query());

		$filter->setSortOrder('DESC');

		$this->assertEquals('desc', $filter->getSortDirection());
	}

	public function test_set_sort_order_is_chainable(): void
	{
		$filter = new SearchFilter(TestModel::query());

		$result = $filter->setSortOrder('asc');

		$this->assertInstanceOf(SearchFilter::class, $result);
		$this->assertSame($filter, $result);
	}

	/**
	 * Test whereIn method.
	 */
	public function test_where_in_adds_condition(): void
	{
		// Create test data
		$this->createTestModel(['status' => 'active']);
		$this->createTestModel(['status' => 'pending']);
		$this->createTestModel(['status' => 'inactive']);

		// Create filter and apply
		$filter = $this->repository->newFilter();
		$filter->whereIn('status', ['active', 'pending']);

		$results = $this->repository->search($filter);

		$this->assertEquals(2, $results->total());
		$statuses = collect($results->items())->pluck('status')->toArray();
		$this->assertContains('active', $statuses);
		$this->assertContains('pending', $statuses);
	}

	public function test_where_in_is_chainable(): void
	{
		$filter = new SearchFilter(TestModel::query());

		$result = $filter->whereIn('id', [1, 2, 3]);

		$this->assertInstanceOf(SearchFilter::class, $result);
		$this->assertSame($filter, $result);
	}

	/**
	 * Test whereNull method.
	 */
	public function test_where_null_filters_null_values(): void
	{
		// Create test data with mixed null/non-null descriptions
		$this->createTestModel(['description' => null]);
		$this->createTestModel(['description' => 'Has description']);
		$this->createTestModel(['description' => null]);

		$filter = $this->repository->newFilter();
		$filter->whereNull('description');

		$results = $this->repository->search($filter);

		$this->assertEquals(2, $results->total());
		foreach ($results->items() as $model) {
			$this->assertNull($model->description);
		}
	}

	/**
	 * Test whereNotNull method.
	 */
	public function test_where_not_null_filters_non_null_values(): void
	{
		// Create test data
		$this->createTestModel(['description' => null]);
		$this->createTestModel(['description' => 'First description']);
		$this->createTestModel(['description' => 'Second description']);

		$filter = $this->repository->newFilter();
		$filter->whereNotNull('description');

		$results = $this->repository->search($filter);

		$this->assertEquals(2, $results->total());
		foreach ($results->items() as $model) {
			$this->assertNotNull($model->description);
		}
	}

	/**
	 * Test whereBetween method.
	 */
	public function test_where_between_filters_range(): void
	{
		// Create test data
		$this->createTestModel(['price' => 50]);
		$this->createTestModel(['price' => 100]);
		$this->createTestModel(['price' => 150]);
		$this->createTestModel(['price' => 200]);
		$this->createTestModel(['price' => 250]);

		$filter = $this->repository->newFilter();
		$filter->whereBetween('price', [100, 200]);

		$results = $this->repository->search($filter);

		$this->assertEquals(3, $results->total());
		$prices = collect($results->items())->pluck('price')->map(function ($price) {
			return (float) $price;
		})->toArray();
		$this->assertContains(100.0, $prices);
		$this->assertContains(150.0, $prices);
		$this->assertContains(200.0, $prices);
	}

	public function test_where_between_with_dates(): void
	{
		$filter = new SearchFilter(TestModel::query());

		$startDate = Carbon::now()->subDays(7);
		$endDate = Carbon::now();

		$filter->whereBetween('created_at', [$startDate, $endDate]);

		// This would filter records created in the last 7 days
		$this->assertInstanceOf(SearchFilter::class, $filter);
	}

	/**
	 * Test multiple orderBy calls.
	 */
	public function test_multiple_order_by_calls(): void
	{
		// Create test data
		$this->createTestModel(['status' => 'active', 'price' => 100, 'name' => 'B']);
		$this->createTestModel(['status' => 'active', 'price' => 200, 'name' => 'A']);
		$this->createTestModel(['status' => 'active', 'price' => 100, 'name' => 'A']);
		$this->createTestModel(['status' => 'inactive', 'price' => 300, 'name' => 'C']);

		$filter = $this->repository->newFilter();

		// Add multiple order by clauses
		$filter->orderBy('status', 'asc')
			   ->orderBy('price', 'desc')
			   ->orderBy('name', 'asc');

		$results = $this->repository->search($filter);

		// First should be sorted by status (active first)
		// Then by price (higher first)
		// Then by name (alphabetically)

		$first = $results->items()[0];
		$this->assertEquals('active', $first->status);
		$this->assertEquals(200, $first->price);
		$this->assertEquals('A', $first->name);
	}

	public function test_order_by_is_chainable(): void
	{
		$filter = new SearchFilter(TestModel::query());

		$result = $filter->orderBy('created_at', 'desc');

		$this->assertInstanceOf(SearchFilter::class, $result);
		$this->assertSame($filter, $result);
	}

	/**
	 * Test combining multiple filter methods.
	 */
	public function test_combined_filters(): void
	{
		// Create test data
		$this->createTestModel(['name' => 'Test Model', 'status' => 'active', 'price' => 200, 'description' => 'Has desc']);
		$this->createTestModel(['name' => 'Another Model', 'status' => 'active', 'price' => 100, 'description' => null]);
		$this->createTestModel(['name' => 'Model Three', 'status' => 'inactive', 'price' => 300, 'description' => 'Has desc']);
		$this->createTestModel(['name' => 'Model Four', 'status' => 'active', 'price' => 300, 'description' => 'Has desc']);

		$filter = $this->repository->newFilter();

		$filter->setSearchBy('Model')
			   ->where('status', 'active')
			   ->whereIn('price', [100, 200, 300])
			   ->whereNotNull('description')
			   ->orderBy('price', 'desc')
			   ->setPerPage(10);

		$results = $this->repository->search($filter);

		// Should find 2 results:
		// - "Test Model" (active, price 200, has description, name contains "Model")
		// - "Model Four" (active, price 300, has description, name contains "Model")
		$this->assertEquals(2, $results->total());

		// Should be ordered by price desc
		$items = $results->items();
		$this->assertEquals(300, $items[0]->price);
		$this->assertEquals(200, $items[1]->price);
	}

	/**
	 * Helper methods.
	 */
	protected function createTestModel(array $attributes = []): TestModel
	{
		$defaults = [
			'name' => 'Test Model',
			'email' => 'test@example.com',
			'status' => 'active',
			'description' => 'Test description',
			'price' => 99.99,
			'quantity' => 10,
			'is_active' => true,
		];

		return TestModel::create(array_merge($defaults, $attributes));
	}
}
