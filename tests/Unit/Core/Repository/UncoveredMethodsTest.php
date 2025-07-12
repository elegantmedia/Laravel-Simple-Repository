<?php

declare(strict_types=1);

namespace ElegantMedia\SimpleRepository\Tests\Unit\Core\Repository;

use ElegantMedia\SimpleRepository\Exceptions\KeyNotFoundInAttributesException;
use ElegantMedia\SimpleRepository\Search\Filters\SearchFilter;
use ElegantMedia\SimpleRepository\Tests\Fixtures\Models\TestModel;
use ElegantMedia\SimpleRepository\Tests\Fixtures\Repositories\TestRepository;
use ElegantMedia\SimpleRepository\Tests\TestCase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class UncoveredMethodsTest extends TestCase
{
	private TestRepository $repository;

	protected function setUp(): void
	{
		parent::setUp();

		$this->repository = new TestRepository();
	}

	/**
	 * Test all method.
	 */
	public function test_all_returns_all_models(): void
	{
		// Create test data
		$model1 = $this->createTestModel(['name' => 'First']);
		$model2 = $this->createTestModel(['name' => 'Second']);
		$model3 = $this->createTestModel(['name' => 'Third']);

		$results = $this->repository->all();

		$this->assertInstanceOf(Collection::class, $results);
		$this->assertCount(3, $results);
		$this->assertEquals(['First', 'Second', 'Third'], $results->pluck('name')->toArray());
	}

	public function test_all_with_empty_relations(): void
	{
		$this->createTestModel(['name' => 'Test']);

		$results = $this->repository->all([]);

		$this->assertInstanceOf(Collection::class, $results);
		$this->assertCount(1, $results);
	}

	public function test_all_with_relations(): void
	{
		$this->createTestModel(['name' => 'Test']);

		// Test with empty relations array
		$results = $this->repository->all([]);

		$this->assertInstanceOf(Collection::class, $results);
		$this->assertCount(1, $results);
	}

	/**
	 * Test paginate method.
	 */
	public function test_paginate_returns_paginated_results(): void
	{
		// Create test data
		for ($i = 1; $i <= 25; $i++) {
			$this->createTestModel(['name' => "Item {$i}"]);
		}

		$results = $this->repository->paginate(10);

		$this->assertInstanceOf(LengthAwarePaginator::class, $results);
		$this->assertEquals(10, $results->perPage());
		$this->assertEquals(25, $results->total());
		$this->assertCount(10, $results->items());
	}

	public function test_paginate_with_custom_per_page(): void
	{
		for ($i = 1; $i <= 15; $i++) {
			$this->createTestModel();
		}

		$results = $this->repository->paginate(5);

		$this->assertEquals(5, $results->perPage());
		$this->assertCount(5, $results->items());
	}

	public function test_paginate_with_relations(): void
	{
		for ($i = 1; $i <= 10; $i++) {
			$this->createTestModel();
		}

		// Test with empty relations array
		$results = $this->repository->paginate(5, []);

		$this->assertInstanceOf(LengthAwarePaginator::class, $results);
		$this->assertCount(5, $results->items());
	}

	public function test_paginate_with_filter(): void
	{
		// Create mixed data
		for ($i = 1; $i <= 5; $i++) {
			$this->createTestModel(['status' => 'active']);
		}
		for ($i = 1; $i <= 5; $i++) {
			$this->createTestModel(['status' => 'inactive']);
		}

		$filter = $this->repository->newFilter();
		$filter->where('status', 'active');

		$results = $this->repository->paginate(10, [], $filter);

		$this->assertEquals(5, $results->total());
		foreach ($results->items() as $item) {
			$this->assertEquals('active', $item->status);
		}
	}

	/**
	 * Test simplePaginate method.
	 */
	public function test_simple_paginate_returns_simple_paginator(): void
	{
		for ($i = 1; $i <= 20; $i++) {
			$this->createTestModel(['name' => "Item {$i}"]);
		}

		$results = $this->repository->simplePaginate(10);

		$this->assertInstanceOf(Paginator::class, $results);
		$this->assertEquals(10, $results->perPage());
		$this->assertCount(10, $results->items());
	}

	public function test_simple_paginate_with_custom_per_page(): void
	{
		for ($i = 1; $i <= 15; $i++) {
			$this->createTestModel();
		}

		$results = $this->repository->simplePaginate(5);

		$this->assertEquals(5, $results->perPage());
		$this->assertCount(5, $results->items());
	}

	public function test_simple_paginate_with_relations(): void
	{
		for ($i = 1; $i <= 10; $i++) {
			$this->createTestModel();
		}

		// Test with empty relations array
		$results = $this->repository->simplePaginate(5, []);

		$this->assertInstanceOf(Paginator::class, $results);
		$this->assertCount(5, $results->items());
	}

	public function test_simple_paginate_with_filter(): void
	{
		// Create mixed data
		for ($i = 1; $i <= 5; $i++) {
			$this->createTestModel(['status' => 'active']);
		}
		for ($i = 1; $i <= 5; $i++) {
			$this->createTestModel(['status' => 'inactive']);
		}

		$filter = $this->repository->newFilter();
		$filter->where('status', 'active');

		$results = $this->repository->simplePaginate(10, [], $filter);

		$this->assertCount(5, $results->items());
		foreach ($results->items() as $item) {
			$this->assertEquals('active', $item->status);
		}
	}

	/**
	 * Test find method with relations.
	 */
	public function test_find_with_relations(): void
	{
		$model = $this->createTestModel(['name' => 'Test Model']);

		// Test with empty relations array
		$found = $this->repository->find($model->id, []);

		$this->assertNotNull($found);
		$this->assertEquals($model->id, $found->id);
		$this->assertEquals('Test Model', $found->name);
	}

	public function test_find_with_empty_relations(): void
	{
		$model = $this->createTestModel();

		$found = $this->repository->find($model->id, []);

		$this->assertNotNull($found);
		$this->assertEquals($model->id, $found->id);
	}

	/**
	 * Test findByField method.
	 */
	public function test_find_by_field_returns_model_when_exists(): void
	{
		$model = $this->createTestModel(['name' => 'Unique Name', 'status' => 'active']);

		$found = $this->repository->findByField('name', 'Unique Name');

		$this->assertNotNull($found);
		$this->assertEquals($model->id, $found->id);
		$this->assertEquals('Unique Name', $found->name);
	}

	public function test_find_by_field_returns_null_when_not_exists(): void
	{
		$found = $this->repository->findByField('name', 'Non Existent');

		$this->assertNull($found);
	}

	public function test_find_by_field_with_relations(): void
	{
		$model = $this->createTestModel(['email' => 'test@example.com']);

		// Test with empty relations array
		$found = $this->repository->findByField('email', 'test@example.com', []);

		$this->assertNotNull($found);
		$this->assertEquals($model->id, $found->id);
	}

	public function test_find_by_field_returns_first_when_multiple(): void
	{
		$model1 = $this->createTestModel(['status' => 'active']);
		$model2 = $this->createTestModel(['status' => 'active']);

		$found = $this->repository->findByField('status', 'active');

		$this->assertNotNull($found);
		$this->assertEquals($model1->id, $found->id);
	}

	/**
	 * Test updateOrCreateByUuid method.
	 */
	public function test_update_or_create_by_uuid_creates_new_model(): void
	{
		$uuid = Str::uuid()->toString();
		$attributes = [
			'uuid' => $uuid,
			'name' => 'New Model',
			'status' => 'active'
		];

		$model = $this->repository->updateOrCreateByUuid($attributes);

		$this->assertInstanceOf(TestModel::class, $model);
		$this->assertEquals($uuid, $model->uuid);
		$this->assertEquals('New Model', $model->name);
		$this->assertEquals('active', $model->status);
		$this->assertTrue($model->exists);
	}

	public function test_update_or_create_by_uuid_updates_existing_model(): void
	{
		$uuid = Str::uuid()->toString();
		$original = $this->createTestModel([
			'uuid' => $uuid,
			'name' => 'Original Name',
			'status' => 'inactive'
		]);

		$attributes = [
			'uuid' => $uuid,
			'name' => 'Updated Name',
			'status' => 'active'
		];

		$model = $this->repository->updateOrCreateByUuid($attributes);

		$this->assertEquals($original->id, $model->id);
		$this->assertEquals($uuid, $model->uuid);
		$this->assertEquals('Updated Name', $model->name);
		$this->assertEquals('active', $model->status);
	}

	public function test_update_or_create_by_uuid_throws_exception_when_uuid_missing(): void
	{
		$this->expectException(KeyNotFoundInAttributesException::class);
		$this->expectExceptionMessage("Key 'uuid' not found in the given attributes array");

		$this->repository->updateOrCreateByUuid([
			'name' => 'No UUID',
			'status' => 'active'
		]);
	}

	public function test_update_or_create_by_uuid_with_additional_attributes(): void
	{
		$uuid = Str::uuid()->toString();
		$attributes = [
			'uuid' => $uuid,
			'name' => 'Model Name',
			'description' => 'Test Description',
			'email' => 'test@example.com'
		];

		$model = $this->repository->updateOrCreateByUuid($attributes);

		$this->assertEquals($uuid, $model->uuid);
		$this->assertEquals('Model Name', $model->name);
		$this->assertEquals('Test Description', $model->description);
		$this->assertEquals('test@example.com', $model->email);
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