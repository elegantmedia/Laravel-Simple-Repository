<?php

declare(strict_types=1);

namespace ElegantMedia\SimpleRepository\Tests\Unit\Repository;

use Carbon\Carbon;
use ElegantMedia\SimpleRepository\Tests\Fixtures\Models\TestModel;
use ElegantMedia\SimpleRepository\Tests\Fixtures\Repositories\TestRepository;
use ElegantMedia\SimpleRepository\Tests\TestCase;
use Illuminate\Support\Collection;

class UtilityMethodsTest extends TestCase
{
	private TestRepository $repository;

	protected function setUp(): void
	{
		parent::setUp();

		$this->repository = new TestRepository();
	}

	/**
	 * Test exists method.
	 */
	public function test_exists_returns_true_when_records_exist(): void
	{
		$this->createTestModel();

		$exists = $this->repository->exists();

		$this->assertTrue($exists);
	}

	public function test_exists_returns_false_when_no_records(): void
	{
		$exists = $this->repository->exists();

		$this->assertFalse($exists);
	}

	public function test_exists_with_where_condition(): void
	{
		$this->createTestModel(['status' => 'active']);
		$this->createTestModel(['status' => 'inactive']);

		$this->assertTrue($this->repository->exists(['status' => 'active']));
		$this->assertTrue($this->repository->exists(['status' => 'inactive']));
		$this->assertFalse($this->repository->exists(['status' => 'pending']));
	}

	public function test_exists_with_multiple_conditions(): void
	{
		$this->createTestModel(['status' => 'active', 'is_active' => true]);
		$this->createTestModel(['status' => 'active', 'is_active' => false]);

		$this->assertTrue($this->repository->exists([
			'status' => 'active',
			'is_active' => true,
		]));

		$this->assertFalse($this->repository->exists([
			'status' => 'inactive',
			'is_active' => true,
		]));
	}

	/**
	 * Test count method.
	 */
	public function test_count_returns_total_records(): void
	{
		$this->createTestModels(5);

		$count = $this->repository->count();

		$this->assertEquals(5, $count);
	}

	public function test_count_returns_zero_when_no_records(): void
	{
		$count = $this->repository->count();

		$this->assertEquals(0, $count);
	}

	public function test_count_with_where_condition(): void
	{
		$this->createTestModel(['status' => 'active']);
		$this->createTestModel(['status' => 'active']);
		$this->createTestModel(['status' => 'inactive']);

		$activeCount = $this->repository->count(['status' => 'active']);
		$inactiveCount = $this->repository->count(['status' => 'inactive']);

		$this->assertEquals(2, $activeCount);
		$this->assertEquals(1, $inactiveCount);
	}

	public function test_count_with_multiple_conditions(): void
	{
		$this->createTestModel(['status' => 'active', 'is_active' => true]);
		$this->createTestModel(['status' => 'active', 'is_active' => false]);
		$this->createTestModel(['status' => 'inactive', 'is_active' => false]);

		$count = $this->repository->count([
			'status' => 'active',
			'is_active' => false,
		]);

		$this->assertEquals(1, $count);
	}

	/**
	 * Test value method.
	 */
	public function test_value_returns_single_column_value(): void
	{
		$model = $this->createTestModel(['name' => 'Specific Name', 'price' => 123.45]);

		$name = $this->repository->value('name');

		$this->assertEquals('Specific Name', $name);
	}

	public function test_value_returns_null_when_no_records(): void
	{
		$value = $this->repository->value('name');

		$this->assertNull($value);
	}

	public function test_value_with_where_condition(): void
	{
		$this->createTestModel(['email' => 'first@example.com', 'price' => 100]);
		$this->createTestModel(['email' => 'second@example.com', 'price' => 200]);

		$price = $this->repository->value('price', ['email' => 'second@example.com']);

		$this->assertEquals(200, $price);
	}

	public function test_value_returns_different_column_types(): void
	{
		$model = $this->createTestModel([
			'price' => 99.99,
			'quantity' => 42,
			'is_active' => true,
			'published_at' => Carbon::parse('2024-01-01'),
		]);

		$this->assertEquals(99.99, $this->repository->value('price'));
		$this->assertEquals(42, $this->repository->value('quantity'));
		$this->assertTrue($this->repository->value('is_active'));
		$this->assertInstanceOf(Carbon::class, $this->repository->value('published_at'));
	}

	/**
	 * Test pluck method.
	 */
	public function test_pluck_returns_collection_of_values(): void
	{
		$this->createTestModel(['name' => 'Model 1']);
		$this->createTestModel(['name' => 'Model 2']);
		$this->createTestModel(['name' => 'Model 3']);

		$names = $this->repository->pluck('name');

		$this->assertInstanceOf(Collection::class, $names);
		$this->assertCount(3, $names);
		$this->assertTrue($names->contains('Model 1'));
		$this->assertTrue($names->contains('Model 2'));
		$this->assertTrue($names->contains('Model 3'));
	}

	public function test_pluck_returns_empty_collection_when_no_records(): void
	{
		$values = $this->repository->pluck('name');

		$this->assertInstanceOf(Collection::class, $values);
		$this->assertCount(0, $values);
		$this->assertTrue($values->isEmpty());
	}

	public function test_pluck_with_key(): void
	{
		$model1 = $this->createTestModel(['name' => 'Model 1', 'email' => 'one@example.com']);
		$model2 = $this->createTestModel(['name' => 'Model 2', 'email' => 'two@example.com']);

		$names = $this->repository->pluck('name', [], 'email');

		$this->assertInstanceOf(Collection::class, $names);
		$this->assertEquals('Model 1', $names['one@example.com']);
		$this->assertEquals('Model 2', $names['two@example.com']);
	}

	public function test_pluck_with_where_condition(): void
	{
		$this->createTestModel(['name' => 'Active 1', 'status' => 'active']);
		$this->createTestModel(['name' => 'Active 2', 'status' => 'active']);
		$this->createTestModel(['name' => 'Inactive 1', 'status' => 'inactive']);

		$activeNames = $this->repository->pluck('name', ['status' => 'active']);

		$this->assertInstanceOf(Collection::class, $activeNames);
		$this->assertCount(2, $activeNames);
		$this->assertTrue($activeNames->contains('Active 1'));
		$this->assertTrue($activeNames->contains('Active 2'));
		$this->assertFalse($activeNames->contains('Inactive 1'));
	}

	/**
	 * Test chunk method.
	 */
	public function test_chunk_processes_records_in_batches(): void
	{
		$this->createTestModels(10);

		$processed = 0;
		$chunkSizes = [];

		$result = $this->repository->chunk(3, function ($models) use (&$processed, &$chunkSizes) {
			$chunkSizes[] = count($models);
			$processed += count($models);
		});

		$this->assertTrue($result);
		$this->assertEquals(10, $processed);
		$this->assertEquals([3, 3, 3, 1], $chunkSizes);
	}

	public function test_chunk_with_where_condition(): void
	{
		$this->createTestModel(['status' => 'active']);
		$this->createTestModel(['status' => 'active']);
		$this->createTestModel(['status' => 'inactive']);

		$processed = 0;

		$this->repository->chunk(2, function ($models) use (&$processed) {
			$processed += count($models);
		}, ['status' => 'active']);

		$this->assertEquals(2, $processed);
	}

	public function test_chunk_can_stop_early(): void
	{
		$this->createTestModels(10);

		$processed = 0;

		$result = $this->repository->chunk(3, function ($models) use (&$processed) {
			$processed += count($models);

			// Stop after first chunk
			return false;
		});

		$this->assertFalse($result);
		$this->assertEquals(3, $processed);
	}

	public function test_chunk_with_empty_result(): void
	{
		$called = false;

		$result = $this->repository->chunk(10, function ($models) use (&$called) {
			$called = true;
		});

		$this->assertTrue($result);
		$this->assertFalse($called);
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

	protected function createTestModels(int $count): void
	{
		for ($i = 0; $i < $count; $i++) {
			$this->createTestModel([
				'name' => "Model {$i}",
				'email' => "test{$i}@example.com",
			]);
		}
	}
}
