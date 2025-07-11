<?php

namespace ElegantMedia\SimpleRepository\Tests\Unit\Repository;

use ElegantMedia\SimpleRepository\Tests\Fixtures\Models\TestModel;
use ElegantMedia\SimpleRepository\Tests\Fixtures\Repositories\TestRepository;
use ElegantMedia\SimpleRepository\Tests\TestCase;

class NewDeleteMethodsTest extends TestCase
{
	private TestRepository $repository;

	protected function setUp(): void
	{
		parent::setUp();

		$this->repository = new TestRepository();
	}
	/**
	 * Test deleteMany method.
	 */
	public function test_delete_many_removes_multiple_records(): void
	{
		$model1 = $this->createTestModel(['name' => 'Delete 1']);
		$model2 = $this->createTestModel(['name' => 'Delete 2']);
		$model3 = $this->createTestModel(['name' => 'Keep Me']);

		$result = $this->repository->deleteMany([$model1->id, $model2->id]);

		$this->assertEquals(2, $result);

		// Verify deletions
		$this->assertNull($this->repository->find($model1->id));
		$this->assertNull($this->repository->find($model2->id));
		$this->assertNotNull($this->repository->find($model3->id));
		
		// Verify count
		$this->assertEquals(1, TestModel::count());
	}

	public function test_delete_many_with_non_existent_ids(): void
	{
		$model = $this->createTestModel();

		$result = $this->repository->deleteMany([$model->id, 999, 1000]);

		$this->assertEquals(1, $result);
		$this->assertNull($this->repository->find($model->id));
		$this->assertEquals(0, TestModel::count());
	}

	public function test_delete_many_with_empty_array(): void
	{
		$this->createTestModel();

		$result = $this->repository->deleteMany([]);

		$this->assertEquals(0, $result);
		$this->assertEquals(1, TestModel::count());
	}

	/**
	 * Test restore method.
	 */
	public function test_restore_recovers_soft_deleted_model(): void
	{
		$model = $this->createTestModel();
		$modelId = $model->id;

		// Soft delete the model
		$model->delete();
		$this->assertNotNull($model->deleted_at);

		// Restore it
		$result = $this->repository->restore($modelId);

		$this->assertTrue($result);

		// Verify restoration
		$restored = $this->repository->find($modelId);
		$this->assertNotNull($restored);
		$this->assertNull($restored->deleted_at);
	}

	public function test_restore_returns_false_for_non_existent_model(): void
	{
		$result = $this->repository->restore(999);

		$this->assertFalse($result);
	}

	public function test_restore_on_non_deleted_model(): void
	{
		$model = $this->createTestModel();

		// Try to restore a non-deleted model
		$result = $this->repository->restore($model->id);

		// Should still return true as the model exists and is not deleted
		$this->assertTrue($result);
	}

	/**
	 * Test forceDelete method.
	 */
	public function test_force_delete_permanently_removes_model(): void
	{
		$model = $this->createTestModel();
		$modelId = $model->id;

		$result = $this->repository->forceDelete($modelId);

		$this->assertTrue($result);

		// Should not find even with trashed
		$this->assertNull($this->repository->find($modelId));
		$this->assertNull($this->repository->findWithTrashed($modelId));

		// Verify complete removal from database
		$this->assertEquals(0, TestModel::withTrashed()->count());
	}

	public function test_force_delete_on_soft_deleted_model(): void
	{
		$model = $this->createTestModel();
		$modelId = $model->id;

		// Soft delete first
		$model->delete();

		// Then force delete
		$result = $this->repository->forceDelete($modelId);

		$this->assertTrue($result);

		// Verify complete removal
		$this->assertNull($this->repository->findWithTrashed($modelId));
		$this->assertEquals(0, TestModel::withTrashed()->count());
	}

	public function test_force_delete_returns_false_for_non_existent(): void
	{
		$result = $this->repository->forceDelete(999);

		$this->assertFalse($result);
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