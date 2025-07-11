<?php

namespace ElegantMedia\SimpleRepository\Tests\Unit\Repository;

use ElegantMedia\SimpleRepository\Tests\Fixtures\Models\TestModel;
use ElegantMedia\SimpleRepository\Tests\Fixtures\Repositories\TestRepository;
use ElegantMedia\SimpleRepository\Tests\TestCase;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Carbon;

class PaginationAndModelTest extends TestCase
{
	private TestRepository $repository;

	protected function setUp(): void
	{
		parent::setUp();

		$this->repository = new TestRepository();
	}

	/**
	 * Test cursorPaginate method.
	 */
	public function test_cursor_paginate_returns_cursor_paginator(): void
	{
		// Create test data
		for ($i = 1; $i <= 25; $i++) {
			$this->createTestModel(['name' => "Model {$i}"]);
		}

		$results = $this->repository->cursorPaginate(10);

		$this->assertInstanceOf(CursorPaginator::class, $results);
		$this->assertEquals(10, $results->perPage());
		$this->assertCount(10, $results->items());
	}

	public function test_cursor_paginate_with_custom_per_page(): void
	{
		// Create test data
		for ($i = 1; $i <= 20; $i++) {
			$this->createTestModel(['name' => "Item {$i}"]);
		}

		$results = $this->repository->cursorPaginate(5);

		$this->assertEquals(5, $results->perPage());
		$this->assertCount(5, $results->items());
	}

	public function test_cursor_paginate_default_per_page(): void
	{
		// Create test data
		for ($i = 1; $i <= 20; $i++) {
			$this->createTestModel();
		}

		$results = $this->repository->cursorPaginate();

		$this->assertEquals(15, $results->perPage());
		$this->assertCount(15, $results->items());
	}

	public function test_cursor_paginate_with_where_conditions(): void
	{
		// Create mixed data
		for ($i = 1; $i <= 10; $i++) {
			$this->createTestModel(['status' => 'active']);
		}
		for ($i = 1; $i <= 5; $i++) {
			$this->createTestModel(['status' => 'inactive']);
		}

		$results = $this->repository
			->where('status', 'active')
			->cursorPaginate(5);

		$this->assertCount(5, $results->items());
		foreach ($results->items() as $item) {
			$this->assertEquals('active', $item->status);
		}
	}

	public function test_cursor_paginate_preserves_ordering(): void
	{
		// Create data with specific order
		$this->createTestModel(['name' => 'First', 'created_at' => Carbon::now()->subDays(3)]);
		$this->createTestModel(['name' => 'Second', 'created_at' => Carbon::now()->subDays(2)]);
		$this->createTestModel(['name' => 'Third', 'created_at' => Carbon::now()->subDays(1)]);

		$results = $this->repository
			->orderBy('created_at', 'asc')
			->cursorPaginate(2);

		$items = $results->items();
		$this->assertEquals('First', $items[0]->name);
		$this->assertEquals('Second', $items[1]->name);
	}

	/**
	 * Test getModel method.
	 */
	public function test_get_model_returns_model_instance(): void
	{
		$model = $this->repository->getModel();

		$this->assertInstanceOf(TestModel::class, $model);
		$this->assertFalse($model->exists); // Should be a new instance
		$this->assertNull($model->id);
	}

	public function test_get_model_returns_same_instance(): void
	{
		$model1 = $this->repository->getModel();
		$model2 = $this->repository->getModel();

		$this->assertSame($model1, $model2);
		$this->assertEquals(get_class($model1), get_class($model2));
	}

	public function test_get_model_can_be_used_for_queries(): void
	{
		// Create test data
		$this->createTestModel(['status' => 'active']);
		$this->createTestModel(['status' => 'inactive']);

		// Use getModel to create a query
		$count = $this->repository->getModel()
			->where('status', 'active')
			->count();

		$this->assertEquals(1, $count);
	}

	public function test_get_model_respects_repository_model_class(): void
	{
		$model = $this->repository->getModel();
		$modelClass = $this->repository->getModelClass();

		$this->assertInstanceOf($modelClass, $model);
		$this->assertEquals(TestModel::class, $modelClass);
	}

	/**
	 * Test with method (already exists, adding comprehensive test).
	 */
	public function test_with_method_for_eager_loading(): void
	{
		$this->createTestModel();

		// Test single relationship
		$query = $this->repository->with('relation');
		$this->assertInstanceOf(get_class($this->repository), $query);

		// Test multiple relationships as array
		$query = $this->repository->with(['relation1', 'relation2']);
		$this->assertInstanceOf(get_class($this->repository), $query);

		// Test chaining
		$results = $this->repository
			->with('relation')
			->where('status', 'active')
			->get();

		$this->assertNotNull($results);
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
