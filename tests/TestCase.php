<?php

declare(strict_types=1);

namespace ElegantMedia\SimpleRepository\Tests;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Events\Dispatcher;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
	protected Capsule $capsule;

	protected function setUp(): void
	{
		parent::setUp();

		$this->setupDatabase();
		$this->setupContainer();
		$this->migrateDatabase();
	}

	protected function tearDown(): void
	{
		$this->capsule->schema()->dropIfExists('test_related_models');
		$this->capsule->schema()->dropIfExists('test_models');

		parent::tearDown();
	}

	protected function setupDatabase(): void
	{
		$this->capsule = new Capsule();

		$this->capsule->addConnection([
			'driver' => 'sqlite',
			'database' => ':memory:',
			'prefix' => '',
		]);

		$this->capsule->setEventDispatcher(new Dispatcher(new Container()));
		$this->capsule->setAsGlobal();
		$this->capsule->bootEloquent();
	}

	protected function migrateDatabase(): void
	{
		$this->capsule->schema()->create('test_models', function (Blueprint $table) {
			$table->id();
			$table->uuid('uuid')->unique()->nullable();
			$table->string('name');
			$table->string('email')->nullable();
			$table->string('status')->default('active');
			$table->text('description')->nullable();
			$table->decimal('price', 10, 2)->nullable();
			$table->integer('quantity')->default(0);
			$table->boolean('is_active')->default(true);
			$table->json('metadata')->nullable();
			$table->timestamp('published_at')->nullable();
			$table->timestamps();
			$table->softDeletes();
		});

		// Create related models table
		$this->capsule->schema()->create('test_related_models', function (Blueprint $table) {
			$table->id();
			$table->foreignId('test_model_id')->constrained('test_models')->cascadeOnDelete();
			$table->string('name');
			$table->string('status')->default('active');
			$table->timestamps();
		});
	}

	protected function setupContainer(): void
	{
		$container = Container::getInstance();

		// Bind request instance
		$container->singleton('request', function () {
			return new \Illuminate\Http\Request();
		});

		// Setup pagination
		Paginator::currentPathResolver(function () {
			return '/test';
		});

		Paginator::currentPageResolver(function ($pageName = 'page') {
			return 1;
		});

		LengthAwarePaginator::currentPathResolver(function () {
			return '/test';
		});

		LengthAwarePaginator::currentPageResolver(function ($pageName = 'page') {
			return 1;
		});
	}
}
