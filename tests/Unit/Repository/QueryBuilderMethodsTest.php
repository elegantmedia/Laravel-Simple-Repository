<?php

namespace ElegantMedia\SimpleRepository\Tests\Unit\Repository;

use ElegantMedia\SimpleRepository\Tests\Fixtures\Models\TestModel;
use ElegantMedia\SimpleRepository\Tests\Fixtures\Repositories\TestRepository;
use ElegantMedia\SimpleRepository\Tests\TestCase;

/**
 * Tests for query builder methods that are now accessed through filters.
 */
class QueryBuilderMethodsTest extends TestCase
{
	private TestRepository $repository;

	protected function setUp(): void
	{
		parent::setUp();

		$this->repository = new TestRepository();
	}

	/**
	 * Test whereIn method through filter.
	 */
	public function test_where_in_filters_by_values(): void
	{
		$model1 = $this->createTestModel(['status' => 'active']);
		$model2 = $this->createTestModel(['status' => 'pending']);
		$model3 = $this->createTestModel(['status' => 'inactive']);
		$model4 = $this->createTestModel(['status' => 'archived']);

		$filter = $this->repository->newFilter();
		$filter->whereIn('status', ['active', 'pending']);
		$filter->setPaginate(false);

		$results = $this->repository->search($filter);

		$this->assertCount(2, $results);
		$statuses = $results->pluck('status')->toArray();
		$this->assertContains('active', $statuses);
		$this->assertContains('pending', $statuses);
	}

	public function test_where_in_returns_empty_when_no_matches(): void
	{
		$this->createTestModel(['price' => 100]);

		$filter = $this->repository->newFilter();
		$filter->whereIn('price', [200, 300]);
		$filter->setPaginate(false);

		$results = $this->repository->search($filter);

		$this->assertCount(0, $results);
	}

	/**
	 * Test where method chaining through filter.
	 */
	public function test_where_method_chains_correctly(): void
	{
		$this->createTestModel(['status' => 'active', 'price' => 100]);
		$this->createTestModel(['status' => 'active', 'price' => 200]);
		$this->createTestModel(['status' => 'inactive', 'price' => 150]);

		$filter = $this->repository->newFilter();
		$filter->where('status', 'active')
			->where('price', '>', 150)
			->setPaginate(false);

		$results = $this->repository->search($filter);

		$this->assertCount(1, $results);
		$this->assertEquals(200, $results->first()->price);
	}

	public function test_where_with_two_parameters(): void
	{
		$this->createTestModel(['name' => 'Test Product']);

		$filter = $this->repository->newFilter();
		$filter->where('name', 'Test Product');
		$filter->setPaginate(false);

		$results = $this->repository->search($filter);

		$this->assertCount(1, $results);
		$this->assertEquals('Test Product', $results->first()->name);
	}

	/**
	 * Test whereHas method through filter.
	 */
	public function test_where_has_filters_by_relationship(): void
	{
		// Create test models
		$model1 = $this->createTestModel(['name' => 'Model 1']);
		$model2 = $this->createTestModel(['name' => 'Model 2']);

		// Add related models
		$model1->relatedModels()->create(['name' => 'Related 1', 'status' => 'active']);
		$model1->relatedModels()->create(['name' => 'Related 2', 'status' => 'inactive']);
		// Model 2 has no related models

		// Test whereHas through filter
		$filter = $this->repository->newFilter();
		$filter->whereHas('relatedModels');
		$filter->setPaginate(false);

		$results = $this->repository->search($filter);

		$this->assertCount(1, $results);
		$this->assertEquals('Model 1', $results->first()->name);
	}

	public function test_where_has_with_callback(): void
	{
		// Create test models
		$model1 = $this->createTestModel(['name' => 'Model 1']);
		$model2 = $this->createTestModel(['name' => 'Model 2']);

		// Add related models with different statuses
		$model1->relatedModels()->create(['name' => 'Related 1', 'status' => 'active']);
		$model2->relatedModels()->create(['name' => 'Related 2', 'status' => 'inactive']);

		// Test whereHas with callback through filter
		$filter = $this->repository->newFilter();
		$filter->whereHas('relatedModels', function ($query) {
			$query->where('status', 'active');
		});
		$filter->setPaginate(false);

		$results = $this->repository->search($filter);

		$this->assertCount(1, $results);
		$this->assertEquals('Model 1', $results->first()->name);
	}

	/**
	 * Test whereDoesntHave method through query builder.
	 */
	public function test_where_doesnt_have_filters_without_relationship(): void
	{
		// Create test models
		$model1 = $this->createTestModel(['name' => 'Model 1']);
		$model2 = $this->createTestModel(['name' => 'Model 2']);

		// Only model1 has related models
		$model1->relatedModels()->create(['name' => 'Related 1']);

		// Since whereDoesntHave is not available in filter, use direct query
		$query = $this->repository->newQuery();
		$results = $query->whereDoesntHave('relatedModels')->get();

		$this->assertCount(1, $results);
		$this->assertEquals('Model 2', $results->first()->name);
	}

	/**
	 * Test has method through query builder.
	 */
	public function test_has_filters_by_relationship_count(): void
	{
		// Create test models
		$model1 = $this->createTestModel(['name' => 'Model 1']);
		$model2 = $this->createTestModel(['name' => 'Model 2']);
		$model3 = $this->createTestModel(['name' => 'Model 3']);

		// Add different numbers of related models
		$model1->relatedModels()->create(['name' => 'Related 1']);

		$model2->relatedModels()->create(['name' => 'Related 2']);
		$model2->relatedModels()->create(['name' => 'Related 3']);

		// Model 3 has no related models

		// Since has() is not available in filter, use direct query
		$query = $this->repository->newQuery();
		$results = $query->has('relatedModels')->get();
		$this->assertCount(2, $results);

		// Test has with specific count
		$query2 = $this->repository->newQuery();
		$results = $query2->has('relatedModels', '>=', 2)->get();
		$this->assertCount(1, $results);
		$this->assertEquals('Model 2', $results->first()->name);
	}

	/**
	 * Test orderBy method through filter.
	 */
	public function test_order_by_sorts_results(): void
	{
		$this->createTestModel(['name' => 'Charlie', 'price' => 200]);
		$this->createTestModel(['name' => 'Alice', 'price' => 100]);
		$this->createTestModel(['name' => 'Bob', 'price' => 150]);

		$filter = $this->repository->newFilter();
		$filter->orderBy('name');
		$filter->setPaginate(false);

		$results = $this->repository->search($filter);

		$this->assertEquals('Alice', $results[0]->name);
		$this->assertEquals('Bob', $results[1]->name);
		$this->assertEquals('Charlie', $results[2]->name);
	}

	public function test_order_by_with_direction(): void
	{
		$this->createTestModel(['price' => 100]);
		$this->createTestModel(['price' => 300]);
		$this->createTestModel(['price' => 200]);

		$filter = $this->repository->newFilter();
		$filter->orderBy('price', 'desc');
		$filter->setPaginate(false);

		$results = $this->repository->search($filter);

		$this->assertEquals(300, $results[0]->price);
		$this->assertEquals(200, $results[1]->price);
		$this->assertEquals(100, $results[2]->price);
	}

	/**
	 * Test limit method through query builder.
	 */
	public function test_limit_restricts_results(): void
	{
		for ($i = 1; $i <= 5; $i++) {
			$this->createTestModel(['name' => "Model {$i}"]);
		}

		// Since limit is not available in filter, use direct query
		$query = $this->repository->newQuery();
		$results = $query->limit(3)->get();

		$this->assertCount(3, $results);
	}

	/**
	 * Test select method through query builder.
	 */
	public function test_select_limits_columns(): void
	{
		$model = $this->createTestModel([
			'name' => 'Selected Model',
			'email' => 'select@example.com',
			'description' => 'Long description text',
		]);

		// Since select is not available in filter, use direct query
		$query = $this->repository->newQuery();
		$result = $query->select(['id', 'name'])->first();

		$this->assertNotNull($result->id);
		$this->assertEquals('Selected Model', $result->name);

		// These columns weren't selected, so they should be null
		$this->assertNull($result->email);
		$this->assertNull($result->description);
	}

	public function test_select_with_multiple_columns(): void
	{
		$this->createTestModel();

		// Since select is not available in filter, use direct query
		$query = $this->repository->newQuery();
		$result = $query->select(['id', 'name', 'status', 'price'])->first();

		$this->assertNotNull($result->id);
		$this->assertNotNull($result->name);
		$this->assertNotNull($result->status);
		$this->assertNotNull($result->price);
		$this->assertNull($result->email);
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
