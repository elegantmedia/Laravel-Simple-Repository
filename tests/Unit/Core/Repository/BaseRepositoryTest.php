<?php

declare(strict_types=1);

namespace ElegantMedia\SimpleRepository\Tests\Unit\Repository;

use ElegantMedia\SimpleRepository\Tests\Fixtures\Models\TestModel;
use ElegantMedia\SimpleRepository\Tests\Fixtures\Repositories\TestRepository;
use ElegantMedia\SimpleRepository\Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BaseRepositoryTest extends TestCase
{
	private TestRepository $repository;

	protected function setUp(): void
	{
		parent::setUp();

		$this->repository = new TestRepository();
	}

	/**
	 * Test model instantiation methods.
	 */
	public function test_new_model_returns_fresh_model_instance(): void
	{
		$model1 = $this->repository->newModel();
		$model2 = $this->repository->newModel();

		$this->assertInstanceOf(TestModel::class, $model1);
		$this->assertInstanceOf(TestModel::class, $model2);
		$this->assertNotSame($model1, $model2);
		$this->assertFalse($model1->exists);
	}

	public function test_new_query_returns_builder_instance(): void
	{
		$query = $this->repository->newQuery();

		$this->assertInstanceOf(Builder::class, $query);
		$this->assertInstanceOf(TestModel::class, $query->getModel());
	}

	public function test_get_model_class_returns_correct_class_name(): void
	{
		$className = $this->repository->getModelClass();

		$this->assertEquals(TestModel::class, $className);
	}

	/**
	 * Test list/index/search methods.
	 */
	public function test_all_returns_empty_collection_when_no_records(): void
	{
		$results = $this->repository->all();

		$this->assertCount(0, $results);
		$this->assertTrue($results->isEmpty());
	}

	public function test_all_returns_all_records(): void
	{
		$this->createTestModels(3);

		$results = $this->repository->all();

		$this->assertCount(3, $results);
	}

	public function test_paginate_returns_paginated_results(): void
	{
		$this->createTestModels(25);

		$results = $this->repository->paginate(10);

		$this->assertEquals(10, $results->perPage());
		$this->assertEquals(25, $results->total());
		$this->assertCount(10, $results->items());
		$this->assertEquals(3, $results->lastPage());
	}

	public function test_paginate_with_custom_per_page(): void
	{
		$this->createTestModels(10);

		$results = $this->repository->paginate(5);

		$this->assertEquals(5, $results->perPage());
		$this->assertCount(5, $results->items());
	}

	public function test_simple_paginate_returns_simple_paginator(): void
	{
		$this->createTestModels(15);

		$results = $this->repository->simplePaginate(10);

		$this->assertEquals(10, $results->perPage());
		$this->assertCount(10, $results->items());
		$this->assertTrue($results->hasMorePages());
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

	protected function createTestModels(int $count, array $attributes = []): void
	{
		for ($i = 0; $i < $count; $i++) {
			$this->createTestModel(array_merge([
				'name' => "Test Model {$i}",
				'email' => "test{$i}@example.com",
			], $attributes));
		}
	}
}
