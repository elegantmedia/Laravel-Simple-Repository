<?php


namespace ElegantMedia\SimpleRepository;


use ElegantMedia\SimpleRepository\Commands\RepositoryMakeCommand;
use Illuminate\Support\ServiceProvider;

class SimpleRepositoryServiceProvider extends ServiceProvider
{

	public function register()
	{
		// register `make:repository` only for local environment
		if ($this->app->environment('local')) {
			$this->commands(RepositoryMakeCommand::class);
		}
	}

}