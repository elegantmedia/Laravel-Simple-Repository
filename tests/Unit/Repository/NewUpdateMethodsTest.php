<?php

namespace ElegantMedia\SimpleRepository\Tests\Unit\Repository;

use ElegantMedia\SimpleRepository\Tests\Fixtures\Models\TestModel;
use ElegantMedia\SimpleRepository\Tests\Fixtures\Repositories\TestRepository;
use ElegantMedia\SimpleRepository\Tests\TestCase;

class NewUpdateMethodsTest extends TestCase
{
	private TestRepository $repository;

	protected function setUp(): void
	{
		parent::setUp();

		$this->repository = new TestRepository();
	}
	/**
	 * Test update method with ID parameter (already tested in CrudMethodsTest).
	 */
	
	/**
	 * Test updateWhere method.
	 */
	public function test_update_where_updates_matching_records(): void
	{
		// Create test data
		$this->createTestModel(['status' => 'pending', 'price' => 100]);
		$this->createTestModel(['status' => 'pending', 'price' => 200]);
		$this->createTestModel(['status' => 'active', 'price' => 300]);

		$result = $this->repository->updateWhere(
			['status' => 'pending'],
			['status' => 'processed', 'price' => 150]
		);

		$this->assertEquals(2, $result);

		// Verify updates
		$pendingCount = TestModel::where('status', 'pending')->count();
		$processedCount = TestModel::where('status', 'processed')->count();
		
		$this->assertEquals(0, $pendingCount);
		$this->assertEquals(2, $processedCount);
		
		// Verify all processed items have the new price
		$processed = TestModel::where('status', 'processed')->get();
		$processed->each(function ($model) {
			$this->assertEquals(150, $model->price);
		});

		// Verify active record wasn't touched
		$active = TestModel::where('status', 'active')->first();
		$this->assertEquals(300, $active->price);
	}

	public function test_update_where_with_multiple_conditions(): void
	{
		$this->createTestModel(['status' => 'pending', 'is_active' => true]);
		$this->createTestModel(['status' => 'pending', 'is_active' => false]);
		$this->createTestModel(['status' => 'active', 'is_active' => true]);

		$result = $this->repository->updateWhere(
			[
				'status' => 'pending',
				'is_active' => true,
			],
			['status' => 'completed']
		);

		$this->assertEquals(1, $result);

		// Verify only the matching record was updated
		$completed = TestModel::where('status', 'completed')->first();
		$this->assertTrue($completed->is_active);
	}

	public function test_update_where_returns_zero_when_no_matches(): void
	{
		$this->createTestModel(['status' => 'active']);

		$result = $this->repository->updateWhere(
			['status' => 'inactive'],
			['price' => 500]
		);

		$this->assertEquals(0, $result);
	}

	public function test_update_where_with_operators(): void
	{
		$this->createTestModel(['price' => 50]);
		$this->createTestModel(['price' => 100]);
		$this->createTestModel(['price' => 150]);

		// Using operators through where array syntax
		$result = $this->repository->updateWhere(
			['price' => ['>', 75]],
			['status' => 'expensive']
		);

		$this->assertEquals(2, $result);

		$expensive = TestModel::where('status', 'expensive')->count();
		$this->assertEquals(2, $expensive);
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