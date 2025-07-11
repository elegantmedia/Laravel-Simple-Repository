<?php

namespace ElegantMedia\SimpleRepository\Tests\Unit\Repository;

use ElegantMedia\SimpleRepository\Tests\Fixtures\Models\TestModel;
use ElegantMedia\SimpleRepository\Tests\Fixtures\Repositories\TestRepository;
use ElegantMedia\SimpleRepository\Tests\TestCase;

class AggregateMethodsTest extends TestCase
{
	private TestRepository $repository;

	protected function setUp(): void
	{
		parent::setUp();

		$this->repository = new TestRepository();
	}
	/**
	 * Test sum method.
	 */
	public function test_sum_calculates_total(): void
	{
		$this->createTestModel(['price' => 100.50]);
		$this->createTestModel(['price' => 200.25]);
		$this->createTestModel(['price' => 150.75]);

		$total = $this->repository->sum('price');

		$this->assertEquals(451.50, $total);
	}

	public function test_sum_with_where_conditions(): void
	{
		$this->createTestModel(['status' => 'active', 'price' => 100]);
		$this->createTestModel(['status' => 'active', 'price' => 200]);
		$this->createTestModel(['status' => 'inactive', 'price' => 300]);

		$total = $this->repository->sum('price', ['status' => 'active']);

		$this->assertEquals(300, $total);
	}

	public function test_sum_returns_zero_when_no_records(): void
	{
		$total = $this->repository->sum('price');

		$this->assertEquals(0, $total);
	}

	/**
	 * Test avg method.
	 */
	public function test_avg_calculates_average(): void
	{
		$this->createTestModel(['quantity' => 10]);
		$this->createTestModel(['quantity' => 20]);
		$this->createTestModel(['quantity' => 30]);

		$average = $this->repository->avg('quantity');

		$this->assertEquals(20, $average);
	}

	public function test_avg_with_where_conditions(): void
	{
		$this->createTestModel(['is_active' => true, 'quantity' => 10]);
		$this->createTestModel(['is_active' => true, 'quantity' => 30]);
		$this->createTestModel(['is_active' => false, 'quantity' => 100]);

		$average = $this->repository->avg('quantity', ['is_active' => true]);

		$this->assertEquals(20, $average);
	}

	public function test_avg_returns_null_when_no_records(): void
	{
		$average = $this->repository->avg('quantity');

		$this->assertNull($average);
	}

	/**
	 * Test min method.
	 */
	public function test_min_finds_minimum_value(): void
	{
		$this->createTestModel(['price' => 50.00]);
		$this->createTestModel(['price' => 25.50]);
		$this->createTestModel(['price' => 100.00]);

		$min = $this->repository->min('price');

		$this->assertEquals(25.50, $min);
	}

	public function test_min_with_where_conditions(): void
	{
		$this->createTestModel(['status' => 'active', 'price' => 100]);
		$this->createTestModel(['status' => 'active', 'price' => 200]);
		$this->createTestModel(['status' => 'inactive', 'price' => 50]);

		$min = $this->repository->min('price', ['status' => 'active']);

		$this->assertEquals(100, $min);
	}

	public function test_min_returns_null_when_no_records(): void
	{
		$min = $this->repository->min('price');

		$this->assertNull($min);
	}

	/**
	 * Test max method.
	 */
	public function test_max_finds_maximum_value(): void
	{
		$this->createTestModel(['quantity' => 5]);
		$this->createTestModel(['quantity' => 15]);
		$this->createTestModel(['quantity' => 10]);

		$max = $this->repository->max('quantity');

		$this->assertEquals(15, $max);
	}

	public function test_max_with_where_conditions(): void
	{
		$this->createTestModel(['is_active' => true, 'quantity' => 10]);
		$this->createTestModel(['is_active' => true, 'quantity' => 20]);
		$this->createTestModel(['is_active' => false, 'quantity' => 50]);

		$max = $this->repository->max('quantity', ['is_active' => true]);

		$this->assertEquals(20, $max);
	}

	public function test_max_returns_null_when_no_records(): void
	{
		$max = $this->repository->max('quantity');

		$this->assertNull($max);
	}

	/**
	 * Test aggregate methods with decimal values.
	 */
	public function test_aggregates_handle_decimal_values(): void
	{
		$this->createTestModel(['price' => 10.25]);
		$this->createTestModel(['price' => 20.50]);
		$this->createTestModel(['price' => 30.75]);

		$sum = $this->repository->sum('price');
		$avg = $this->repository->avg('price');
		$min = $this->repository->min('price');
		$max = $this->repository->max('price');

		$this->assertEquals(61.50, $sum);
		$this->assertEquals(20.50, $avg);
		$this->assertEquals(10.25, $min);
		$this->assertEquals(30.75, $max);
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