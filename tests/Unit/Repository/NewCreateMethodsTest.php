<?php

namespace ElegantMedia\SimpleRepository\Tests\Unit\Repository;

use ElegantMedia\SimpleRepository\Tests\Fixtures\Models\TestModel;
use ElegantMedia\SimpleRepository\Tests\Fixtures\Repositories\TestRepository;
use ElegantMedia\SimpleRepository\Tests\TestCase;

class NewCreateMethodsTest extends TestCase
{
	private TestRepository $repository;

	protected function setUp(): void
	{
		parent::setUp();

		$this->repository = new TestRepository();
	}
	/**
	 * Test createMany method.
	 */
	public function test_create_many_creates_multiple_records(): void
	{
		$records = [
			[
				'name' => 'Bulk Model 1',
				'email' => 'bulk1@example.com',
				'status' => 'active',
				'price' => 100.00,
				'quantity' => 5,
			],
			[
				'name' => 'Bulk Model 2',
				'email' => 'bulk2@example.com',
				'status' => 'inactive',
				'price' => 200.00,
				'quantity' => 10,
			],
			[
				'name' => 'Bulk Model 3',
				'email' => 'bulk3@example.com',
				'status' => 'active',
				'price' => 300.00,
				'quantity' => 15,
			],
		];

		$results = $this->repository->createMany($records);

		$this->assertCount(3, $results);
		$this->assertEquals('Bulk Model 1', $results[0]->name);
		$this->assertEquals('bulk2@example.com', $results[1]->email);
		$this->assertEquals(300.00, $results[2]->price);

		// Verify in database
		$this->assertEquals(3, TestModel::count());
	}

	public function test_create_many_returns_empty_collection_for_empty_input(): void
	{
		$results = $this->repository->createMany([]);

		$this->assertCount(0, $results);
		$this->assertEquals(0, TestModel::count());
	}

	/**
	 * Test updateOrCreate method.
	 */
	public function test_update_or_create_updates_existing_record(): void
	{
		$existing = $this->createTestModel([
			'email' => 'existing@example.com',
			'name' => 'Original Name',
		]);

		$result = $this->repository->updateOrCreate(
			['email' => 'existing@example.com'],
			['name' => 'Updated Name', 'status' => 'updated']
		);

		$this->assertEquals($existing->id, $result->id);
		$this->assertEquals('Updated Name', $result->name);
		$this->assertEquals('updated', $result->status);
		$this->assertEquals('existing@example.com', $result->email);

		// Verify only one record exists
		$this->assertEquals(1, TestModel::count());
	}

	public function test_update_or_create_creates_new_record(): void
	{
		$result = $this->repository->updateOrCreate(
			['email' => 'new@example.com'],
			[
				'name' => 'New Model',
				'status' => 'active',
				'price' => 150.00,
				'quantity' => 20,
			]
		);

		$this->assertNotNull($result->id);
		$this->assertEquals('new@example.com', $result->email);
		$this->assertEquals('New Model', $result->name);
		$this->assertEquals('active', $result->status);
		$this->assertEquals(150.00, $result->price);

		// Verify record exists in database
		$this->assertEquals(1, TestModel::count());
	}

	public function test_update_or_create_with_multiple_attributes(): void
	{
		$existing = $this->createTestModel([
			'email' => 'multi@example.com',
			'status' => 'active',
		]);

		$result = $this->repository->updateOrCreate(
			[
				'email' => 'multi@example.com',
				'status' => 'active',
			],
			[
				'name' => 'Multi Update',
				'price' => 250.00,
			]
		);

		$this->assertEquals($existing->id, $result->id);
		$this->assertEquals('Multi Update', $result->name);
		$this->assertEquals(250.00, $result->price);
		$this->assertEquals('active', $result->status); // Should remain unchanged
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