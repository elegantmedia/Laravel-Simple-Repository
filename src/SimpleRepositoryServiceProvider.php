<?php

declare(strict_types=1);

namespace ElegantMedia\SimpleRepository;

use ElegantMedia\SimpleRepository\Commands\RepositoryMakeCommand;
use Illuminate\Support\ServiceProvider;

class SimpleRepositoryServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 */
	public function register(): void
	{
		$this->mergeConfigFrom(
			__DIR__ . '/../config/simple-repository.php',
			'simple-repository'
		);
	}

	/**
	 * Bootstrap the service provider.
	 */
	public function boot(): void
	{
		if ($this->app->runningInConsole()) {
			// Register commands
			$this->commands([
				RepositoryMakeCommand::class,
			]);

			// Publish config
			$this->publishes([
				__DIR__ . '/../config/simple-repository.php' => config_path('simple-repository.php'),
			], 'simple-repository-config');
		}
	}
}
