<?php

namespace ElegantMedia\SimpleRepository\Tests\Unit\Repository;

use ElegantMedia\SimpleRepository\Tests\Fixtures\Models\TestModel;
use ElegantMedia\SimpleRepository\Tests\Fixtures\Repositories\TestRepository;
use ElegantMedia\SimpleRepository\Tests\TestCase;

class NewUtilityMethodsTest extends TestCase
{
	private TestRepository $repository;

	protected function setUp(): void
	{
		parent::setUp();

		$this->repository = new TestRepository();
	}

	/**
	 * Test random method.
	 */
	public function test_random_returns_single_model(): void
	{
		// Create multiple models
		for ($i = 1; $i <= 5; $i++) {
			$this->createTestModel(['name' => "Model {$i}"]);
		}

		$result = $this->repository->random();

		$this->assertInstanceOf(TestModel::class, $result);
		$this->assertStringStartsWith('Model', $result->name);
	}

	public function test_random_returns_multiple_models(): void
	{
		// Create 10 models
		for ($i = 1; $i <= 10; $i++) {
			$this->createTestModel(['name' => "Random {$i}"]);
		}

		$results = $this->repository->random(3);

		$this->assertCount(3, $results);
		$results->each(function ($model) {
			$this->assertInstanceOf(TestModel::class, $model);
			$this->assertStringStartsWith('Random', $model->name);
		});

		// Verify we got different models (with high probability)
		$ids = $results->pluck('id')->unique();
		$this->assertCount(3, $ids);
	}

	public function test_random_returns_all_when_count_exceeds_total(): void
	{
		// Create only 3 models
		for ($i = 1; $i <= 3; $i++) {
			$this->createTestModel();
		}

		$results = $this->repository->random(5);

		// Should return all 3 models
		$this->assertCount(3, $results);
	}

	public function test_random_returns_empty_when_no_records(): void
	{
		$result = $this->repository->random();

		$this->assertNull($result);
	}

	/**
	 * Test existsWhere method.
	 */
	public function test_exists_where_returns_true_when_matches(): void
	{
		$this->createTestModel(['status' => 'active', 'price' => 100]);

		$exists = $this->repository->existsWhere(['status' => 'active']);

		$this->assertTrue($exists);
	}

	public function test_exists_where_returns_false_when_no_matches(): void
	{
		$this->createTestModel(['status' => 'active']);

		$exists = $this->repository->existsWhere(['status' => 'inactive']);

		$this->assertFalse($exists);
	}

	public function test_exists_where_with_multiple_conditions(): void
	{
		$this->createTestModel([
			'status' => 'active',
			'is_active' => true,
			'price' => 100,
		]);

		$exists = $this->repository->existsWhere([
			'status' => 'active',
			'is_active' => true,
			'price' => 100,
		]);

		$this->assertTrue($exists);

		// One condition doesn't match
		$exists = $this->repository->existsWhere([
			'status' => 'active',
			'is_active' => false,
			'price' => 100,
		]);

		$this->assertFalse($exists);
	}

	/**
	 * Test countWhere method.
	 */
	public function test_count_where_returns_matching_count(): void
	{
		$this->createTestModel(['status' => 'active']);
		$this->createTestModel(['status' => 'active']);
		$this->createTestModel(['status' => 'inactive']);

		$count = $this->repository->countWhere(['status' => 'active']);

		$this->assertEquals(2, $count);
	}

	public function test_count_where_returns_zero_when_no_matches(): void
	{
		$this->createTestModel(['status' => 'active']);

		$count = $this->repository->countWhere(['status' => 'pending']);

		$this->assertEquals(0, $count);
	}

	public function test_count_where_with_multiple_conditions(): void
	{
		$this->createTestModel(['status' => 'active', 'is_active' => true]);
		$this->createTestModel(['status' => 'active', 'is_active' => false]);
		$this->createTestModel(['status' => 'inactive', 'is_active' => true]);

		$count = $this->repository->countWhere([
			'status' => 'active',
			'is_active' => true,
		]);

		$this->assertEquals(1, $count);
	}

	public function test_count_where_with_operators(): void
	{
		$this->createTestModel(['price' => 50]);
		$this->createTestModel(['price' => 100]);
		$this->createTestModel(['price' => 150]);
		$this->createTestModel(['price' => 200]);

		// Using operator syntax
		$count = $this->repository->countWhere([
			'price' => ['>=', 100],
		]);

		$this->assertEquals(3, $count);
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
