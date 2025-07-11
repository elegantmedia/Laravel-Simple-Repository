<?php

declare(strict_types=1);

namespace ElegantMedia\SimpleRepository\Tests\Unit\Repository;

use ElegantMedia\SimpleRepository\Tests\Fixtures\Models\TestModel;
use ElegantMedia\SimpleRepository\Tests\Fixtures\Repositories\TestRepository;
use ElegantMedia\SimpleRepository\Tests\TestCase;
use Illuminate\Support\Str;

class CrudMethodsTest extends TestCase
{
	private TestRepository $repository;

	protected function setUp(): void
	{
		parent::setUp();

		$this->repository = new TestRepository();
	}

	/**
	 * Test create method.
	 */
	public function test_create_creates_new_model(): void
	{
		$attributes = [
			'name' => 'New Model',
			'email' => 'new@example.com',
			'status' => 'pending',
			'price' => 150.50,
			'quantity' => 5,
			'is_active' => false,
		];

		$model = $this->repository->create($attributes);

		$this->assertInstanceOf(TestModel::class, $model);
		$this->assertTrue($model->exists);
		$this->assertEquals('New Model', $model->name);
		$this->assertEquals('new@example.com', $model->email);
		$this->assertEquals('pending', $model->status);
		$this->assertEquals(150.50, $model->price);
		$this->assertEquals(5, $model->quantity);
		$this->assertFalse($model->is_active);

		// Verify it's in the database
		$found = $this->repository->find($model->id);
		$this->assertNotNull($found);
		$this->assertEquals($model->id, $found->id);
	}

	public function test_create_with_minimal_attributes(): void
	{
		$model = $this->repository->create(['name' => 'Minimal Model']);

		$this->assertTrue($model->exists);
		$this->assertEquals('Minimal Model', $model->name);
		$this->assertEquals('active', $model->status); // Default value from database
		$this->assertTrue($model->is_active); // Default value from database
		$this->assertEquals(0, $model->quantity); // Default value from database
	}

	/**
	 * Test update method.
	 */
	public function test_update_updates_existing_model(): void
	{
		$model = $this->createTestModel();
		$originalName = $model->name;

		$result = $this->repository->updateById($model->id, [
			'name' => 'Updated Name',
			'status' => 'inactive',
		]);

		$this->assertTrue($result);

		// Verify in database
		$fresh = $this->repository->find($model->id);
		$this->assertEquals('Updated Name', $fresh->name);
		$this->assertEquals('inactive', $fresh->status);
	}

	public function test_update_returns_true_on_success(): void
	{
		$model = $this->createTestModel();

		$result = $this->repository->updateById($model->id, ['name' => 'Test Update']);

		$this->assertTrue($result);

		// Verify the update
		$fresh = $this->repository->find($model->id);
		$this->assertEquals('Test Update', $fresh->name);
	}

	public function test_update_model_updates_existing_model(): void
	{
		$model = $this->createTestModel();

		$result = $this->repository->updateModel($model, [
			'name' => 'Updated Model',
			'status' => 'inactive',
		]);

		$this->assertTrue($result);
		$this->assertEquals('Updated Model', $model->name);
		$this->assertEquals('inactive', $model->status);

		// Verify in database
		$fresh = $this->repository->find($model->id);
		$this->assertEquals('Updated Model', $fresh->name);
		$this->assertEquals('inactive', $fresh->status);
	}

	/**
	 * Test updateById method.
	 */
	public function test_update_by_id_updates_existing_model(): void
	{
		$model = $this->createTestModel();

		$result = $this->repository->updateById(
			$model->id,
			['name' => 'Updated By ID', 'price' => 299.99]
		);

		$this->assertTrue($result);

		// Verify in database
		$fresh = $this->repository->find($model->id);
		$this->assertEquals('Updated By ID', $fresh->name);
		$this->assertEquals(299.99, $fresh->price);
	}

	public function test_update_by_id_returns_false_when_not_found(): void
	{
		$result = $this->repository->updateById(999, ['name' => 'Test']);

		$this->assertFalse($result);
	}

	public function test_update_by_id_with_custom_column(): void
	{
		$uuid = (string) Str::uuid();
		$model = $this->createTestModel(['uuid' => $uuid]);

		$result = $this->repository->updateById(
			$uuid,
			['name' => 'Updated By UUID'],
			'uuid'
		);

		$this->assertTrue($result);

		$fresh = $this->repository->find($model->id);
		$this->assertEquals('Updated By UUID', $fresh->name);
	}

	/**
	 * Test updateOrCreate method.
	 */
	public function test_update_or_create_updates_existing_model(): void
	{
		$model = $this->createTestModel(['email' => 'existing@example.com']);

		$result = $this->repository->updateOrCreate(
			['email' => 'existing@example.com'],
			['name' => 'Updated Name', 'status' => 'updated']
		);

		$this->assertEquals($model->id, $result->id);
		$this->assertEquals('Updated Name', $result->name);
		$this->assertEquals('updated', $result->status);
	}

	public function test_update_or_create_creates_new_model(): void
	{
		$result = $this->repository->updateOrCreate(
			['email' => 'new@example.com'],
			['name' => 'New Model']
		);

		$this->assertTrue($result->exists);
		$this->assertEquals('new@example.com', $result->email);
		$this->assertEquals('New Model', $result->name);
	}

	public function test_update_or_create_by_id_creates_when_null(): void
	{
		$model = $this->repository->updateOrCreateById(null, [
			'email' => 'new@example.com',
			'name' => 'Created Model',
		]);

		$this->assertTrue($model->exists);
		$this->assertEquals('new@example.com', $model->email);
		$this->assertEquals('Created Model', $model->name);
	}

	public function test_update_or_create_by_id_updates_existing(): void
	{
		$existing = $this->createTestModel();

		$result = $this->repository->updateOrCreateById($existing->id, [
			'name' => 'Updated Name',
			'status' => 'updated',
		]);

		$this->assertEquals($existing->id, $result->id);
		$this->assertEquals('Updated Name', $result->name);
		$this->assertEquals('updated', $result->status);
	}

	/**
	 * Test updateOrCreateByUuid method.
	 */
	public function test_update_or_create_by_uuid(): void
	{
		$uuid = (string) Str::uuid();

		$model = $this->repository->updateOrCreateByUuid([
			'uuid' => $uuid,
			'name' => 'UUID Model',
		]);

		$this->assertTrue($model->exists);
		$this->assertEquals($uuid, $model->uuid);
		$this->assertEquals('UUID Model', $model->name);

		// Update the same model
		$updated = $this->repository->updateOrCreateByUuid([
			'uuid' => $uuid,
			'name' => 'Updated UUID Model',
		]);

		$this->assertEquals($model->id, $updated->id);
		$this->assertEquals('Updated UUID Model', $updated->name);
	}

	/**
	 * Test save method.
	 */
	public function test_save_saves_model_changes(): void
	{
		$model = $this->createTestModel();
		$model->name = 'Modified Name';
		$model->price = 123.45;

		$result = $this->repository->save($model);

		$this->assertTrue($result);

		// Verify in database
		$fresh = $this->repository->find($model->id);
		$this->assertEquals('Modified Name', $fresh->name);
		$this->assertEquals(123.45, $fresh->price);
	}

	/**
	 * Test delete method.
	 */
	public function test_delete_removes_existing_model(): void
	{
		$model = $this->createTestModel();
		$id = $model->id;

		$result = $this->repository->delete($id);

		$this->assertTrue($result);

		// Verify it's removed from database
		$found = $this->repository->find($id);
		$this->assertNull($found);
	}

	public function test_delete_returns_false_when_not_found(): void
	{
		$result = $this->repository->delete(999);

		$this->assertFalse($result);
	}

	/**
	 * Test deleteWhere method.
	 */
	public function test_delete_where_removes_matching_records(): void
	{
		// Create multiple records
		$this->createTestModel(['status' => 'draft']);
		$this->createTestModel(['status' => 'draft']);
		$this->createTestModel(['status' => 'active']);

		$deletedCount = $this->repository->deleteWhere(['status' => 'draft']);

		$this->assertEquals(2, $deletedCount);

		// Verify only active record remains
		$remaining = $this->repository->all();
		$this->assertCount(1, $remaining);
		$this->assertEquals('active', $remaining->first()->status);
	}

	public function test_delete_where_with_multiple_conditions(): void
	{
		$this->createTestModel(['status' => 'active', 'is_active' => true]);
		$this->createTestModel(['status' => 'active', 'is_active' => false]);
		$this->createTestModel(['status' => 'inactive', 'is_active' => false]);

		$deletedCount = $this->repository->deleteWhere([
			'status' => 'active',
			'is_active' => false,
		]);

		$this->assertEquals(1, $deletedCount);

		$remaining = $this->repository->all();
		$this->assertCount(2, $remaining);
	}

	public function test_delete_where_with_operator(): void
	{
		$this->createTestModel(['price' => 50]);
		$this->createTestModel(['price' => 100]);
		$this->createTestModel(['price' => 150]);

		$deletedCount = $this->repository->deleteWhere([
			['price', '>', 75],
		]);

		$this->assertEquals(2, $deletedCount);

		$remaining = $this->repository->all();
		$this->assertCount(1, $remaining);
		$this->assertEquals(50, $remaining->first()->price);
	}

	public function test_delete_where_returns_zero_when_no_matches(): void
	{
		$this->createTestModel(['status' => 'active']);

		$deletedCount = $this->repository->deleteWhere(['status' => 'nonexistent']);

		$this->assertEquals(0, $deletedCount);

		// Verify nothing was deleted
		$all = $this->repository->all();
		$this->assertCount(1, $all);
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
