<?php

declare(strict_types=1);

namespace ElegantMedia\SimpleRepository\Tests\Unit\Repository;

use ElegantMedia\SimpleRepository\Tests\Fixtures\Models\TestModel;
use ElegantMedia\SimpleRepository\Tests\Fixtures\Repositories\TestRepository;
use ElegantMedia\SimpleRepository\Tests\TestCase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

class FindMethodsTest extends TestCase
{
	private TestRepository $repository;

	protected function setUp(): void
	{
		parent::setUp();

		$this->repository = new TestRepository();
	}

	/**
	 * Test find method.
	 */
	public function test_find_returns_model_when_exists(): void
	{
		$model = $this->createTestModel();

		$found = $this->repository->find($model->id);

		$this->assertInstanceOf(TestModel::class, $found);
		$this->assertEquals($model->id, $found->id);
		$this->assertEquals($model->name, $found->name);
	}

	public function test_find_returns_null_when_not_exists(): void
	{
		$found = $this->repository->find(999);

		$this->assertNull($found);
	}

	public function test_find_with_relationships(): void
	{
		$model = $this->createTestModel();

		// Test that the method accepts the parameter - relationships won't be loaded since they don't exist
		$found = $this->repository->find($model->id, []);

		$this->assertInstanceOf(TestModel::class, $found);
		$this->assertEquals($model->id, $found->id);
	}

	/**
	 * Test findByUuid method.
	 */
	public function test_find_by_uuid_returns_model_when_exists(): void
	{
		$uuid = (string) Str::uuid();
		$model = $this->createTestModel(['uuid' => $uuid]);

		$found = $this->repository->findByUuid($uuid);

		$this->assertInstanceOf(TestModel::class, $found);
		$this->assertEquals($uuid, $found->uuid);
	}

	public function test_find_by_uuid_returns_null_when_not_exists(): void
	{
		$found = $this->repository->findByUuid('non-existent-uuid');

		$this->assertNull($found);
	}

	public function test_find_by_uuid_with_relationships(): void
	{
		$uuid = (string) Str::uuid();
		$model = $this->createTestModel(['uuid' => $uuid]);

		// Test that the method accepts the parameter - relationships won't be loaded since they don't exist
		$found = $this->repository->findByUuid($uuid, []);

		$this->assertInstanceOf(TestModel::class, $found);
		$this->assertEquals($uuid, $found->uuid);
	}

	/**
	 * Test findByField method.
	 */
	public function test_find_by_field_returns_model_when_exists(): void
	{
		$model = $this->createTestModel(['email' => 'unique@example.com']);

		$found = $this->repository->findByField('email', 'unique@example.com');

		$this->assertInstanceOf(TestModel::class, $found);
		$this->assertEquals('unique@example.com', $found->email);
	}

	public function test_find_by_field_returns_null_when_not_exists(): void
	{
		$found = $this->repository->findByField('email', 'nonexistent@example.com');

		$this->assertNull($found);
	}

	public function test_find_by_field_with_relationships(): void
	{
		$model = $this->createTestModel(['status' => 'special']);

		// Test that the method accepts the parameter - relationships won't be loaded since they don't exist
		$found = $this->repository->findByField('status', 'special', []);

		$this->assertInstanceOf(TestModel::class, $found);
		$this->assertEquals('special', $found->status);
	}

	/**
	 * Test findOrCreate method.
	 */
	public function test_find_or_create_returns_existing_model(): void
	{
		$existing = $this->createTestModel(['email' => 'existing@example.com']);

		$model = $this->repository->findOrCreate(
			['email' => 'existing@example.com'],
			['name' => 'New Name']
		);

		$this->assertEquals($existing->id, $model->id);
		$this->assertEquals($existing->name, $model->name); // Name should not be updated
	}

	public function test_find_or_create_creates_new_model(): void
	{
		$model = $this->repository->findOrCreate(
			['email' => 'new@example.com'],
			['name' => 'New Model']
		);

		$this->assertTrue($model->exists);
		$this->assertEquals('new@example.com', $model->email);
		$this->assertEquals('New Model', $model->name);
	}

	public function test_find_or_create_by_id_creates_when_null(): void
	{
		$model = $this->repository->findOrCreateById(null, [
			'email' => 'new@example.com',
			'name' => 'Created Model',
		]);

		$this->assertTrue($model->exists);
		$this->assertEquals('new@example.com', $model->email);
		$this->assertEquals('Created Model', $model->name);
	}

	public function test_find_or_create_by_id_returns_existing(): void
	{
		$existing = $this->createTestModel();

		$model = $this->repository->findOrCreateById($existing->id, [
			'email' => 'different@example.com',
			'name' => 'Different Name',
		]);

		$this->assertEquals($existing->id, $model->id);
		$this->assertEquals($existing->email, $model->email); // Should not be updated
	}

	/**
	 * Test findOrFail method.
	 */
	public function test_find_or_fail_returns_model_when_exists(): void
	{
		$model = $this->createTestModel();

		$found = $this->repository->findOrFail($model->id);

		$this->assertInstanceOf(TestModel::class, $found);
		$this->assertEquals($model->id, $found->id);
	}

	public function test_find_or_fail_throws_exception_when_not_exists(): void
	{
		$this->expectException(ModelNotFoundException::class);

		$this->repository->findOrFail(999);
	}

	/**
	 * Test findByAttribute method.
	 */
	public function test_find_by_attribute_updates_existing_model(): void
	{
		$model = $this->createTestModel([
			'email' => 'test@example.com',
			'name' => 'Original Name',
		]);

		$updated = $this->repository->findByAttribute(
			'email',
			'test@example.com',
			['name' => 'Updated Name']
		);

		$this->assertNotNull($updated);
		$this->assertEquals($model->id, $updated->id);
		$this->assertEquals('Updated Name', $updated->name);

		// Verify in database
		$fresh = $this->repository->find($model->id);
		$this->assertEquals('Updated Name', $fresh->name);
	}

	public function test_find_by_attribute_returns_null_when_not_found(): void
	{
		$result = $this->repository->findByAttribute(
			'email',
			'nonexistent@example.com',
			['name' => 'New Name']
		);

		$this->assertNull($result);
	}

	/**
	 * Test firstOrNew method.
	 */
	public function test_first_or_new_returns_existing_model(): void
	{
		$existing = $this->createTestModel(['email' => 'existing@example.com']);

		$model = $this->repository->firstOrNew(['email' => 'existing@example.com']);

		$this->assertTrue($model->exists);
		$this->assertEquals($existing->id, $model->id);
	}

	public function test_first_or_new_returns_new_model_instance(): void
	{
		$model = $this->repository->firstOrNew(
			['email' => 'new@example.com'],
			['name' => 'New Model']
		);

		$this->assertFalse($model->exists);
		$this->assertEquals('new@example.com', $model->email);
		$this->assertEquals('New Model', $model->name);
	}

	/**
	 * Test firstOrCreate method.
	 */
	public function test_first_or_create_returns_existing_model(): void
	{
		$existing = $this->createTestModel(['email' => 'existing@example.com']);

		$model = $this->repository->firstOrCreate(['email' => 'existing@example.com']);

		$this->assertTrue($model->exists);
		$this->assertEquals($existing->id, $model->id);
	}

	public function test_first_or_create_creates_new_model(): void
	{
		$model = $this->repository->firstOrCreate(
			['email' => 'new@example.com'],
			['name' => 'New Model']
		);

		$this->assertTrue($model->exists);
		$this->assertEquals('new@example.com', $model->email);
		$this->assertEquals('New Model', $model->name);

		// Verify it's in the database
		$found = $this->repository->find($model->id);
		$this->assertNotNull($found);
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
