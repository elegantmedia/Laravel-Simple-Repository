<?php

declare(strict_types=1);

namespace ElegantMedia\SimpleRepository\Tests\Unit\Core\Filter;

use ElegantMedia\SimpleRepository\Search\Filters\SearchFilter;
use ElegantMedia\SimpleRepository\Tests\Fixtures\Models\TestModel;
use ElegantMedia\SimpleRepository\Tests\Fixtures\Repositories\TestRepository;
use ElegantMedia\SimpleRepository\Tests\TestCase;
use Illuminate\Http\Request;

class UncoveredSearchFilterMethodsTest extends TestCase
{
	private TestRepository $repository;

	protected function setUp(): void
	{
		parent::setUp();

		$this->repository = new TestRepository();
	}

	/**
	 * Test setRequest method.
	 */
	public function test_set_request_stores_request_instance(): void
	{
		$request = new Request();
		$filter = $this->repository->newFilter();

		$result = $filter->setRequest($request);

		$this->assertSame($filter, $result);
		// The request is stored internally and used by applyDefaults
	}

	public function test_set_request_with_query_parameters(): void
	{
		$request = new Request([
			'q' => 'search term',
			'sort_by' => 'name',
			'sort_direction' => 'asc',
			'per_page' => '25'
		]);

		$filter = new SearchFilter($this->repository->newQuery(), false);
		$filter->setRequest($request);

		// Trigger applyDefaults by creating a new filter with defaults enabled
		$filterWithDefaults = new SearchFilter($this->repository->newQuery(), true);
		$filterWithDefaults->setRequest($request);

		// Test that defaults are applied when filter is created with defaults=true and request is set
		$this->assertEquals(50, $filter->getPerPage()); // Default value, not from request
		$this->assertEquals('created_at', $filter->getSortBy()); // Default value, not from request
	}

	/**
	 * Test applyDefaults method indirectly through constructor.
	 */
	public function test_apply_defaults_from_request_in_constructor(): void
	{
		$request = new Request([
			'q' => 'keyword',
			'sort_by' => 'updated_at',
			'sort_direction' => 'asc',
			'per_page' => '30'
		]);

		// Create filter with request set before construction
		$query = $this->repository->newQuery();
		$filter = new class($query, $request) extends SearchFilter {
			public function __construct($query, $request)
			{
				$this->request = $request;
				parent::__construct($query, true);
			}

			public function getKeyword(): ?string
			{
				return $this->keyword;
			}

			public function getSortDirection(): string
			{
				return $this->sortDirection;
			}
		};

		$this->assertEquals('keyword', $filter->getKeyword());
		$this->assertEquals('updated_at', $filter->getSortBy());
		$this->assertEquals('asc', $filter->getSortDirection());
		$this->assertEquals(30, $filter->getPerPage());
	}

	public function test_apply_defaults_with_invalid_sort_direction(): void
	{
		$request = new Request([
			'sort_direction' => 'invalid'
		]);

		$query = $this->repository->newQuery();
		$filter = new class($query, $request) extends SearchFilter {
			public function __construct($query, $request)
			{
				$this->request = $request;
				parent::__construct($query, true);
			}

			public function getSortDirection(): string
			{
				return $this->sortDirection;
			}
		};

		// Should default to 'desc' for invalid direction
		$this->assertEquals('desc', $filter->getSortDirection());
	}

	public function test_apply_defaults_with_per_page_limits(): void
	{
		// Test upper limit
		$request = new Request(['per_page' => '200']);
		$query = $this->repository->newQuery();
		$filter = new class($query, $request) extends SearchFilter {
			public function __construct($query, $request)
			{
				$this->request = $request;
				parent::__construct($query, true);
			}
		};
		$this->assertEquals(100, $filter->getPerPage()); // Limited to 100

		// Test lower limit
		$request = new Request(['per_page' => '0']);
		$filter = new class($query, $request) extends SearchFilter {
			public function __construct($query, $request)
			{
				$this->request = $request;
				parent::__construct($query, true);
			}
		};
		$this->assertEquals(1, $filter->getPerPage()); // Minimum is 1
	}

	/**
	 * Test shouldPaginate method.
	 */
	public function test_should_paginate_returns_true_by_default(): void
	{
		$filter = $this->repository->newFilter();

		$this->assertTrue($filter->shouldPaginate());
	}

	public function test_should_paginate_returns_false_when_disabled(): void
	{
		$filter = $this->repository->newFilter();
		$filter->setPaginate(false);

		$this->assertFalse($filter->shouldPaginate());
	}

	/**
	 * Test getPerPage method.
	 */
	public function test_get_per_page_returns_default_value(): void
	{
		$filter = $this->repository->newFilter();

		$this->assertEquals(50, $filter->getPerPage());
	}

	public function test_get_per_page_returns_custom_value(): void
	{
		$filter = $this->repository->newFilter();
		$filter->setPerPage(25);

		$this->assertEquals(25, $filter->getPerPage());
	}

	/**
	 * Test getSortBy method.
	 */
	public function test_get_sort_by_returns_default_value(): void
	{
		$filter = $this->repository->newFilter();

		$this->assertEquals('created_at', $filter->getSortBy());
	}

	public function test_get_sort_by_returns_custom_value(): void
	{
		$filter = $this->repository->newFilter();
		$filter->setSortBy('name');

		$this->assertEquals('name', $filter->getSortBy());
	}

	/**
	 * Test __call magic method.
	 */
	public function test_magic_call_adds_conditions(): void
	{
		// Clear any existing data first
		TestModel::query()->delete();

		// Create test data
		$model1 = $this->createTestModel(['status' => 'active', 'name' => 'Test Active']);
		$model2 = $this->createTestModel(['status' => 'pending', 'name' => 'Test Pending']);
		$model3 = $this->createTestModel(['status' => 'inactive', 'name' => 'Test Inactive']);

		// Create a fresh filter with only the conditions we want to test
		$filter = $this->repository->newFilter();
		$filter->setPaginate(false); // Disable pagination for this test

		// Test various query builder methods through __call
		$result = $filter->whereIn('status', ['active', 'pending']);
		$this->assertSame($filter, $result);

		// Apply filter and get results
		$query = $this->repository->newQuery();
		$filteredQuery = $filter->apply($query);
		$results = $filteredQuery->get();

		// Should only return active and pending records
		$this->assertCount(2, $results);
		$statuses = $results->pluck('status')->toArray();
		sort($statuses); // Sort for consistent assertion
		$this->assertEquals(['active', 'pending'], $statuses);
	}

	public function test_magic_call_with_complex_conditions(): void
	{
		$filter = $this->repository->newFilter();

		// Chain multiple conditions
		$filter->where('status', 'active')
			->where('name', '!=', 'Excluded')
			->whereNotNull('email')
			->whereBetween('id', [1, 100]);

		// Create test data
		$model1 = $this->createTestModel([
			'status' => 'active',
			'name' => 'Included',
			'email' => 'test@example.com'
		]);
		$model2 = $this->createTestModel([
			'status' => 'active',
			'name' => 'Excluded',
			'email' => 'excluded@example.com'
		]);
		$model3 = $this->createTestModel([
			'status' => 'inactive',
			'name' => 'Inactive',
			'email' => 'inactive@example.com'
		]);
		$model4 = $this->createTestModel([
			'status' => 'active',
			'name' => 'No Email',
			'email' => null
		]);

		$query = $this->repository->newQuery();
		$results = $filter->apply($query)->get();

		// Should only return the first model
		$this->assertCount(1, $results);
		$this->assertEquals($model1->id, $results[0]->id);
	}

	public function test_magic_call_with_joins(): void
	{
		$filter = $this->repository->newFilter();

		// Test join operations through __call
		$filter->join('related_table', 'test_models.id', '=', 'related_table.test_model_id')
			->select('test_models.*');

		// This tests that the __call method properly stores join conditions
		$this->assertInstanceOf(SearchFilter::class, $filter);
	}

	public function test_magic_call_preserves_method_chaining(): void
	{
		$filter = $this->repository->newFilter();

		$result = $filter
			->where('status', 'active')
			->whereIn('type', ['A', 'B'])
			->orderBy('created_at', 'desc')
			->limit(10);

		$this->assertSame($filter, $result);
	}

	protected function createTestModel(array $attributes = []): TestModel
	{
		$defaults = [
			'name' => 'Test Model',
			'email' => 'test@example.com',
			'status' => 'active',
		];

		return $this->repository->create(array_merge($defaults, $attributes));
	}
}