<?php

namespace ElegantMedia\SimpleRepository\Tests\Unit\Repository;

use ElegantMedia\SimpleRepository\Exceptions\RepositoryException;
use ElegantMedia\SimpleRepository\Tests\Fixtures\Models\TestModel;
use ElegantMedia\SimpleRepository\Tests\Fixtures\Repositories\TestRepository;
use ElegantMedia\SimpleRepository\Tests\TestCase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

class NewFindMethodsTest extends TestCase
{
	private TestRepository $repository;

	protected function setUp(): void
	{
		parent::setUp();

		$this->repository = new TestRepository();
	}
	/**
	 * Test findBy method.
	 */
	public function test_find_by_returns_model_when_exists(): void
	{
		$model = $this->createTestModel(['email' => 'findby@example.com']);

		$result = $this->repository->findBy('email', 'findby@example.com');

		$this->assertInstanceOf(TestModel::class, $result);
		$this->assertEquals($model->id, $result->id);
		$this->assertEquals('findby@example.com', $result->email);
	}

	public function test_find_by_returns_null_when_not_exists(): void
	{
		$result = $this->repository->findBy('email', 'notfound@example.com');

		$this->assertNull($result);
	}

	/**
	 * Test findByOrFail method.
	 */
	public function test_find_by_or_fail_returns_model_when_exists(): void
	{
		$model = $this->createTestModel(['name' => 'Unique Name']);

		$result = $this->repository->findByOrFail('name', 'Unique Name');

		$this->assertInstanceOf(TestModel::class, $result);
		$this->assertEquals($model->id, $result->id);
	}

	public function test_find_by_or_fail_throws_exception_when_not_exists(): void
	{
		$this->expectException(ModelNotFoundException::class);

		$this->repository->findByOrFail('name', 'Non Existent');
	}

	/**
	 * Test findWithTrashed method.
	 */
	public function test_find_with_trashed_returns_soft_deleted_model(): void
	{
		// Create a model with soft delete capability
		$model = $this->createTestModel();
		$modelId = $model->id;
		
		// Soft delete it
		$model->delete();

		// Should not find with regular find
		$this->assertNull($this->repository->find($modelId));

		// Should find with findWithTrashed
		$result = $this->repository->findWithTrashed($modelId);
		
		$this->assertInstanceOf(TestModel::class, $result);
		$this->assertEquals($modelId, $result->id);
		$this->assertNotNull($result->deleted_at);
	}

	public function test_find_with_trashed_returns_non_deleted_model(): void
	{
		$model = $this->createTestModel();

		$result = $this->repository->findWithTrashed($model->id);

		$this->assertInstanceOf(TestModel::class, $result);
		$this->assertEquals($model->id, $result->id);
		$this->assertNull($result->deleted_at);
	}

	/**
	 * Test findOnlyTrashed method.
	 */
	public function test_find_only_trashed_returns_only_soft_deleted(): void
	{
		$model = $this->createTestModel();
		$modelId = $model->id;
		
		// Should not find non-deleted model
		$result = $this->repository->findOnlyTrashed($modelId);
		$this->assertNull($result);

		// Soft delete it
		$model->delete();

		// Should find after deletion
		$result = $this->repository->findOnlyTrashed($modelId);
		
		$this->assertInstanceOf(TestModel::class, $result);
		$this->assertEquals($modelId, $result->id);
		$this->assertNotNull($result->deleted_at);
	}

	/**
	 * Test findMany method.
	 */
	public function test_find_many_returns_collection_of_models(): void
	{
		$model1 = $this->createTestModel(['name' => 'Model 1']);
		$model2 = $this->createTestModel(['name' => 'Model 2']);
		$model3 = $this->createTestModel(['name' => 'Model 3']);

		$results = $this->repository->findMany([$model1->id, $model2->id, 999]);

		$this->assertCount(2, $results);
		$this->assertEquals('Model 1', $results->first()->name);
		$this->assertEquals('Model 2', $results->last()->name);
	}

	public function test_find_many_returns_empty_collection_when_none_found(): void
	{
		$results = $this->repository->findMany([999, 1000, 1001]);

		$this->assertCount(0, $results);
	}

	/**
	 * Test findManyBy method.
	 */
	public function test_find_many_by_returns_collection_matching_value(): void
	{
		$this->createTestModel(['status' => 'active']);
		$this->createTestModel(['status' => 'active']);
		$this->createTestModel(['status' => 'inactive']);

		$results = $this->repository->findManyBy('status', 'active');

		$this->assertCount(2, $results);
		$results->each(function ($model) {
			$this->assertEquals('active', $model->status);
		});
	}

	public function test_find_many_by_returns_empty_collection_when_none_match(): void
	{
		$this->createTestModel(['status' => 'active']);

		$results = $this->repository->findManyBy('status', 'pending');

		$this->assertCount(0, $results);
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