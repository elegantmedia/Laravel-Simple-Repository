<?php

namespace ElegantMedia\SimpleRepository\Tests\Unit\Repository;

use ElegantMedia\SimpleRepository\Tests\Fixtures\Models\TestModel;
use ElegantMedia\SimpleRepository\Tests\Fixtures\Repositories\TestRepository;
use ElegantMedia\SimpleRepository\Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchMethodsTest extends TestCase
{
	private TestRepository $repository;

	protected function setUp(): void
	{
		parent::setUp();

		$this->repository = new TestRepository();
	}

	/**
	 * Test searchByTerm method.
	 */
	public function test_search_by_term_finds_matching_records(): void
	{
		$this->createTestModel(['name' => 'John Doe', 'email' => 'john@example.com']);
		$this->createTestModel(['name' => 'Jane Smith', 'email' => 'jane@example.com']);
		$this->createTestModel(['name' => 'Bob Johnson', 'email' => 'john.bob@example.com']);

		// Search by name
		$results = $this->repository->searchByTerm('John');

		$this->assertCount(2, $results);
		$names = $results->pluck('name')->toArray();
		$this->assertContains('John Doe', $names);
		$this->assertContains('Bob Johnson', $names);
	}

	public function test_search_by_term_is_case_insensitive(): void
	{
		$this->createTestModel(['name' => 'UPPERCASE NAME']);
		$this->createTestModel(['name' => 'lowercase name']);
		$this->createTestModel(['name' => 'MiXeD CaSe']);

		$results = $this->repository->searchByTerm('case');

		$this->assertCount(3, $results); // All three contain "case"
	}

	public function test_search_by_term_returns_empty_when_no_matches(): void
	{
		$this->createTestModel(['name' => 'Test Model']);

		$results = $this->repository->searchByTerm('nonexistent');

		$this->assertCount(0, $results);
	}

	/**
	 * Test searchPaginated method.
	 */
	public function test_search_paginated_returns_paginated_results(): void
	{
		// Create 25 models with searchable content
		for ($i = 1; $i <= 25; $i++) {
			$this->createTestModel([
				'name' => "User {$i}",
				'email' => "user{$i}@example.com",
			]);
		}

		$results = $this->repository->searchPaginated('User', 10);

		$this->assertInstanceOf(LengthAwarePaginator::class, $results);
		$this->assertEquals(10, $results->perPage());
		$this->assertEquals(25, $results->total());
		$this->assertCount(10, $results->items());
		$this->assertEquals(3, $results->lastPage());
	}

	public function test_search_paginated_with_custom_per_page(): void
	{
		for ($i = 1; $i <= 15; $i++) {
			$this->createTestModel(['name' => "Product {$i}"]);
		}

		$results = $this->repository->searchPaginated('Product', 5);

		$this->assertEquals(5, $results->perPage());
		$this->assertEquals(15, $results->total());
		$this->assertCount(5, $results->items());
	}

	public function test_search_paginated_with_no_results(): void
	{
		$this->createTestModel(['name' => 'Test Model']);

		$results = $this->repository->searchPaginated('nonexistent', 10);

		$this->assertInstanceOf(LengthAwarePaginator::class, $results);
		$this->assertEquals(0, $results->total());
		$this->assertCount(0, $results->items());
	}

	/**
	 * Test search with filter returns builder functionality.
	 */
	public function test_search_with_filter_and_additional_conditions(): void
	{
		$this->createTestModel(['name' => 'Searchable Item']);

		$filter = $this->repository->newFilter();
		$filter->setKeyword('Searchable');
		$filter->where('status', 'active');
		$filter->setPaginate(false);

		$results = $this->repository->search($filter);

		$this->assertCount(1, $results);
		$this->assertEquals('Searchable Item', $results->first()->name);
	}

	public function test_search_filter_can_be_chained(): void
	{
		$this->createTestModel(['name' => 'First Match', 'price' => 100]);
		$this->createTestModel(['name' => 'Second Match', 'price' => 200]);
		$this->createTestModel(['name' => 'Third Item', 'price' => 150]);

		$filter = $this->repository->newFilter();
		$filter->setKeyword('Match')
			->where('price', '>', 150)
			->orderBy('price', 'desc')
			->setPaginate(false);

		$results = $this->repository->search($filter);

		$this->assertCount(1, $results);
		$this->assertEquals('Second Match', $results->first()->name);
		$this->assertEquals(200, $results->first()->price);
	}

	public function test_direct_query_builder_with_search(): void
	{
		$this->createTestModel(['name' => 'Test Search', 'email' => 'test@example.com']);

		// For complex queries with select, use direct query builder
		$query = $this->repository->newQuery();
		$result = $query->where('name', 'LIKE', '%Test%')
			->select(['id', 'name'])
			->first();

		$this->assertNotNull($result->id);
		$this->assertEquals('Test Search', $result->name);
		$this->assertNull($result->email); // Not selected
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
