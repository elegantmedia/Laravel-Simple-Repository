<?php

namespace ElegantMedia\SimpleRepository\Tests\Unit\Repository;

use ElegantMedia\SimpleRepository\Tests\Fixtures\Models\TestModel;
use ElegantMedia\SimpleRepository\Tests\Fixtures\Models\TestRelatedModel;
use ElegantMedia\SimpleRepository\Tests\Fixtures\Repositories\TestRepository;
use ElegantMedia\SimpleRepository\Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;

class QueryBuilderMethodsTest extends TestCase
{
	private TestRepository $repository;

	protected function setUp(): void
	{
		parent::setUp();

		$this->repository = new TestRepository();
	}
	/**
	 * Test whereIn method.
	 */
	public function test_where_in_filters_by_values(): void
	{
		$model1 = $this->createTestModel(['status' => 'active']);
		$model2 = $this->createTestModel(['status' => 'pending']);
		$model3 = $this->createTestModel(['status' => 'inactive']);
		$model4 = $this->createTestModel(['status' => 'archived']);

		$results = $this->repository->whereIn('status', ['active', 'pending'])->get();

		$this->assertCount(2, $results);
		$statuses = $results->pluck('status')->toArray();
		$this->assertContains('active', $statuses);
		$this->assertContains('pending', $statuses);
	}

	public function test_where_in_returns_empty_when_no_matches(): void
	{
		$this->createTestModel(['price' => 100]);

		$results = $this->repository->whereIn('price', [200, 300])->get();

		$this->assertCount(0, $results);
	}

	/**
	 * Test where method chaining.
	 */
	public function test_where_method_chains_correctly(): void
	{
		$this->createTestModel(['status' => 'active', 'price' => 100]);
		$this->createTestModel(['status' => 'active', 'price' => 200]);
		$this->createTestModel(['status' => 'inactive', 'price' => 150]);

		$results = $this->repository
			->where('status', 'active')
			->where('price', '>', 150)
			->get();

		$this->assertCount(1, $results);
		$this->assertEquals(200, $results->first()->price);
	}

	public function test_where_with_two_parameters(): void
	{
		$this->createTestModel(['name' => 'Test Product']);

		$result = $this->repository->where('name', 'Test Product')->first();

		$this->assertNotNull($result);
		$this->assertEquals('Test Product', $result->name);
	}

	/**
	 * Test whereHas method.
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

		// Test whereHas
		$results = $this->repository->whereHas('relatedModels')->get();
		
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

		// Test whereHas with callback for active related models
		$results = $this->repository->whereHas('relatedModels', function ($query) {
			$query->where('status', 'active');
		})->get();
		
		$this->assertCount(1, $results);
		$this->assertEquals('Model 1', $results->first()->name);
	}

	/**
	 * Test whereDoesntHave method.
	 */
	public function test_where_doesnt_have_filters_without_relationship(): void
	{
		// Create test models
		$model1 = $this->createTestModel(['name' => 'Model 1']);
		$model2 = $this->createTestModel(['name' => 'Model 2']);
		
		// Only model1 has related models
		$model1->relatedModels()->create(['name' => 'Related 1']);

		// Test whereDoesntHave
		$results = $this->repository->whereDoesntHave('relatedModels')->get();
		
		$this->assertCount(1, $results);
		$this->assertEquals('Model 2', $results->first()->name);
	}

	/**
	 * Test has method.
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

		// Test has with default (at least 1)
		$results = $this->repository->has('relatedModels')->get();
		$this->assertCount(2, $results);
		
		// Test has with specific count
		$results = $this->repository->has('relatedModels', '>=', 2)->get();
		$this->assertCount(1, $results);
		$this->assertEquals('Model 2', $results->first()->name);
	}

	/**
	 * Test orderBy method.
	 */
	public function test_order_by_sorts_results(): void
	{
		$this->createTestModel(['name' => 'Charlie', 'price' => 200]);
		$this->createTestModel(['name' => 'Alice', 'price' => 100]);
		$this->createTestModel(['name' => 'Bob', 'price' => 150]);

		$results = $this->repository->orderBy('name')->get();

		$this->assertEquals('Alice', $results[0]->name);
		$this->assertEquals('Bob', $results[1]->name);
		$this->assertEquals('Charlie', $results[2]->name);
	}

	public function test_order_by_with_direction(): void
	{
		$this->createTestModel(['price' => 100]);
		$this->createTestModel(['price' => 300]);
		$this->createTestModel(['price' => 200]);

		$results = $this->repository->orderBy('price', 'desc')->get();

		$this->assertEquals(300, $results[0]->price);
		$this->assertEquals(200, $results[1]->price);
		$this->assertEquals(100, $results[2]->price);
	}

	/**
	 * Test limit method.
	 */
	public function test_limit_restricts_results(): void
	{
		for ($i = 1; $i <= 5; $i++) {
			$this->createTestModel(['name' => "Model {$i}"]);
		}

		$results = $this->repository->limit(3)->get();

		$this->assertCount(3, $results);
	}

	/**
	 * Test select method.
	 */
	public function test_select_limits_columns(): void
	{
		$model = $this->createTestModel([
			'name' => 'Selected Model',
			'email' => 'select@example.com',
			'description' => 'Long description text',
		]);

		$result = $this->repository->select(['id', 'name'])->first();

		$this->assertNotNull($result->id);
		$this->assertEquals('Selected Model', $result->name);
		
		// These columns weren't selected, so they should be null
		$this->assertNull($result->email);
		$this->assertNull($result->description);
	}

	public function test_select_with_multiple_columns(): void
	{
		$this->createTestModel();

		$result = $this->repository->select(['id', 'name', 'status', 'price'])->first();

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