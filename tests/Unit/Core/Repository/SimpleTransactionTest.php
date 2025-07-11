<?php

declare(strict_types=1);

namespace ElegantMedia\SimpleRepository\Tests\Unit\Repository;

use ElegantMedia\SimpleRepository\Tests\Fixtures\Models\TestModel;
use ElegantMedia\SimpleRepository\Tests\Fixtures\Repositories\TestRepository;
use ElegantMedia\SimpleRepository\Tests\TestCase;
use Exception;

class SimpleTransactionTest extends TestCase
{
	private TestRepository $repository;

	protected function setUp(): void
	{
		parent::setUp();
		$this->repository = new TestRepository(new TestModel());
	}

	/**
	 * Test manual transaction with commit.
	 */
	public function testManualTransactionCommit(): void
	{
		$this->repository->beginTransaction();
		
		$model = $this->repository->create(['name' => 'Transaction Test']);
		$this->assertNotNull($model);
		
		// Data should be visible within transaction
		$found = $this->repository->find($model->id);
		$this->assertNotNull($found);
		
		$this->repository->commit();
		
		// Data should still be visible after commit
		$foundAfterCommit = $this->repository->find($model->id);
		$this->assertNotNull($foundAfterCommit);
		$this->assertEquals('Transaction Test', $foundAfterCommit->name);
	}

	/**
	 * Test manual transaction with rollback.
	 */
	public function testManualTransactionRollback(): void
	{
		$this->repository->beginTransaction();
		
		$model = $this->repository->create(['name' => 'To Be Rolled Back']);
		$this->assertNotNull($model);
		$id = $model->id;
		
		// Data should be visible within transaction
		$found = $this->repository->find($id);
		$this->assertNotNull($found);
		
		$this->repository->rollback();
		
		// Data should not exist after rollback
		$foundAfterRollback = $this->repository->find($id);
		$this->assertNull($foundAfterRollback);
	}

	/**
	 * Test transaction callback with success.
	 */
	public function testTransactionCallbackSuccess(): void
	{
		$result = $this->repository->transaction(function ($repo) {
			$model1 = $repo->create(['name' => 'Model 1']);
			$model2 = $repo->create(['name' => 'Model 2']);
			
			return [$model1->id, $model2->id];
		});
		
		$this->assertCount(2, $result);
		
		// Both models should exist
		$this->assertNotNull($this->repository->find($result[0]));
		$this->assertNotNull($this->repository->find($result[1]));
	}

	/**
	 * Test transaction callback with exception.
	 */
	public function testTransactionCallbackException(): void
	{
		$modelId = null;
		
		try {
			$this->repository->transaction(function ($repo) use (&$modelId) {
				$model = $repo->create(['name' => 'Should Rollback']);
				$modelId = $model->id;
				
				throw new Exception('Test exception');
			});
		} catch (Exception $e) {
			$this->assertEquals('Test exception', $e->getMessage());
		}
		
		// Model should not exist due to rollback
		if ($modelId) {
			$this->assertNull($this->repository->find($modelId));
		}
	}

	/**
	 * Test withTransaction enables auto-transaction.
	 */
	public function testWithTransactionFeature(): void
	{
		// Enable auto-transaction and create multiple models
		$this->repository->withTransaction();
		
		// Create a model - should be auto-wrapped in transaction
		$model1 = $this->repository->create(['name' => 'Auto Transaction 1']);
		$this->assertNotNull($model1);
		
		// Update it - should also be wrapped
		$this->repository->updateModel($model1, ['name' => 'Updated Auto 1']);
		
		// Create another
		$model2 = $this->repository->create(['name' => 'Auto Transaction 2']);
		$this->assertNotNull($model2);
		
		// All operations should have succeeded
		$this->assertEquals('Updated Auto 1', $this->repository->find($model1->id)->name);
		$this->assertEquals('Auto Transaction 2', $this->repository->find($model2->id)->name);
		
		// Disable auto-transaction
		$this->repository->withTransaction(false);
		
		// This should work without transaction
		$model3 = $this->repository->create(['name' => 'No Auto Transaction']);
		$this->assertNotNull($model3);
	}

	/**
	 * Test nested transactions.
	 */
	public function testNestedTransactions(): void
	{
		$this->assertEquals(0, $this->repository->transactionLevel());
		
		$this->repository->beginTransaction();
		$this->assertEquals(1, $this->repository->transactionLevel());
		
		$this->repository->beginTransaction();
		$this->assertEquals(2, $this->repository->transactionLevel());
		
		$model = $this->repository->create(['name' => 'Nested']);
		
		$this->repository->commit();
		$this->assertEquals(1, $this->repository->transactionLevel());
		
		$this->repository->commit();
		$this->assertEquals(0, $this->repository->transactionLevel());
		
		// Model should exist
		$this->assertNotNull($this->repository->find($model->id));
	}

	/**
	 * Test complex scenario with multiple operations in transaction.
	 */
	public function testComplexTransactionOperations(): void
	{
		$result = $this->repository->transaction(function ($repo) {
			// Create
			$model1 = $repo->create(['name' => 'User 1', 'email' => 'user1@test.com']);
			$model2 = $repo->create(['name' => 'User 2', 'email' => 'user2@test.com']);
			
			// Update
			$repo->updateModel($model1, ['status' => 'updated']);
			
			// Batch create
			$batch = $repo->createMany([
				['name' => 'Batch 1', 'email' => 'batch1@test.com'],
				['name' => 'Batch 2', 'email' => 'batch2@test.com'],
			]);
			
			// Delete one
			$repo->delete($model2->id);
			
			// Count remaining
			return $repo->count();
		});
		
		// Should have 3 models (model1, batch1, batch2)
		$this->assertEquals(3, $result);
		
		// Verify specific models
		$user1 = $this->repository->findByField('email', 'user1@test.com');
		$this->assertNotNull($user1);
		$this->assertEquals('updated', $user1->status);
		
		$user2 = $this->repository->findByField('email', 'user2@test.com');
		$this->assertNull($user2); // Should be deleted
		
		$batch1 = $this->repository->findByField('email', 'batch1@test.com');
		$this->assertNotNull($batch1);
	}
}