<?php

declare(strict_types=1);

namespace ElegantMedia\SimpleRepository\Tests\Unit\Repository;

use ElegantMedia\SimpleRepository\Search\Filters\SearchFilter;
use ElegantMedia\SimpleRepository\Tests\Fixtures\Repositories\TestRepository;
use ElegantMedia\SimpleRepository\Tests\TestCase;

class SearchFilterTest extends TestCase
{
	private TestRepository $repository;

	protected function setUp(): void
	{
		parent::setUp();

		$this->repository = new TestRepository();

		// Create test data
		$this->createTestModels();
	}

	public function test_search_returns_all_results_when_no_filter(): void
	{
		$results = $this->repository->search();

		$this->assertCount(5, $results);
	}

	public function test_search_with_custom_filter(): void
	{
		$filter = $this->repository->newSearchFilter();
		$filter->where('status', 'active');

		$results = $this->repository->search($filter);

		$this->assertCount(3, $results);
	}

	public function test_new_search_filter_returns_filter_instance(): void
	{
		$filter = $this->repository->newSearchFilter();

		$this->assertInstanceOf(SearchFilter::class, $filter);
	}

	public function test_new_search_filter_without_defaults(): void
	{
		$filter = $this->repository->newSearchFilter(false);

		$this->assertInstanceOf(SearchFilter::class, $filter);
	}

	public function test_search_filter_with_where_conditions(): void
	{
		$filter = $this->repository->newSearchFilter();
		$filter->where('name', 'Product 1')
			   ->where('is_active', true);

		$results = $this->repository->search($filter);

		$this->assertCount(1, $results);
		$this->assertEquals('Product 1', $results->first()->name);
	}

	public function test_search_filter_with_multiple_where(): void
	{
		$filter = $this->repository->newSearchFilter();
		$filter->where('status', 'active')
			   ->where('is_active', true);

		$results = $this->repository->search($filter);

		$this->assertCount(2, $results);
	}

	public function test_search_filter_with_sort(): void
	{
		$filter = $this->repository->newSearchFilter();
		$filter->setSortBy('price')->setSortDirection('desc');

		$results = $this->repository->search($filter);

		$this->assertEquals(200.00, $results->first()->price);
		$this->assertEquals(20.00, $results->last()->price);
	}

	public function test_search_filter_with_pagination_disabled(): void
	{
		$filter = $this->repository->newSearchFilter();
		$filter->paginate(false);

		$results = $this->repository->search($filter);

		$this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $results);
		$this->assertCount(5, $results);
	}

	public function test_search_filter_with_pagination(): void
	{
		$filter = $this->repository->newSearchFilter();
		$filter->paginate(true)->setPerPage(2);

		$results = $this->repository->search($filter);

		$this->assertEquals(2, $results->perPage());
		$this->assertEquals(5, $results->total());
	}

	public function test_paginate_with_filter(): void
	{
		$filter = $this->repository->newSearchFilter();
		$filter->where('status', 'active');

		$results = $this->repository->paginate(2, [], $filter);

		$this->assertEquals(2, $results->perPage());
		$this->assertEquals(3, $results->total());
	}

	public function test_simple_paginate_with_filter(): void
	{
		$filter = $this->repository->newSearchFilter();
		$filter->where('is_active', true);

		$results = $this->repository->simplePaginate(2, [], $filter);

		$this->assertEquals(2, $results->perPage());
		$this->assertCount(2, $results->items());
	}

	protected function createTestModels(): void
	{
		$models = [
			['name' => 'Product 1', 'status' => 'active', 'price' => 100.00, 'is_active' => true],
			['name' => 'Product 2', 'status' => 'pending', 'price' => 50.00, 'is_active' => true],
			['name' => 'Product 3', 'status' => 'active', 'price' => 200.00, 'is_active' => false],
			['name' => 'Product 4', 'status' => 'inactive', 'price' => 20.00, 'is_active' => false],
			['name' => 'Product 5', 'status' => 'active', 'price' => 150.00, 'is_active' => true],
		];

		foreach ($models as $model) {
			$this->repository->create($model);
		}
	}
}
