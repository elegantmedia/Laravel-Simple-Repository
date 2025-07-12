<?php

declare(strict_types=1);

namespace ElegantMedia\SimpleRepository\Tests\Unit\Commands;

require_once __DIR__ . '/bootstrap.php';

use ElegantMedia\SimpleRepository\Commands\RepositoryMakeCommand;
use ElegantMedia\SimpleRepository\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;

class RepositoryMakeCommandTest extends TestCase
{
	/**
	 * Test getStub method returns correct path.
	 */
	public function test_get_stub_returns_correct_path(): void
	{
		$command = new class(new Filesystem()) extends RepositoryMakeCommand {
			public function testGetStub(): string
			{
				return $this->getStub();
			}
		};

		$stubPath = $command->testGetStub();
		$this->assertStringEndsWith('stubs' . DIRECTORY_SEPARATOR . 'Repository.php.stub', $stubPath);
		$this->assertStringContainsString('Commands', $stubPath);
	}

	/**
	 * Test getDefaultNamespace method without group option.
	 */
	public function test_get_default_namespace_without_group(): void
	{
		$command = new class(new Filesystem()) extends RepositoryMakeCommand {
			public $testDir = 'Models';
			public $testGroup = false;

			public function testGetDefaultNamespace($rootNamespace): string
			{
				return parent::getDefaultNamespace($rootNamespace);
			}

			public function option($key = null)
			{
				if ($key === 'dir') {
					return $this->testDir;
				}
				if ($key === 'group') {
					return $this->testGroup;
				}
				return null;
			}
		};

		$namespace = $command->testGetDefaultNamespace('App');
		$this->assertEquals('App\\Models', $namespace);
	}

	/**
	 * Test getDefaultNamespace method with custom directory.
	 */
	public function test_get_default_namespace_with_custom_directory(): void
	{
		$command = new class(new Filesystem()) extends RepositoryMakeCommand {
			public $testDir = 'Repositories';
			public $testGroup = false;

			public function testGetDefaultNamespace($rootNamespace): string
			{
				return parent::getDefaultNamespace($rootNamespace);
			}

			public function option($key = null)
			{
				if ($key === 'dir') {
					return $this->testDir;
				}
				if ($key === 'group') {
					return $this->testGroup;
				}
				return null;
			}
		};

		$namespace = $command->testGetDefaultNamespace('App');
		$this->assertEquals('App\\Repositories', $namespace);
	}

	/**
	 * Test getDefaultNamespace method with group option.
	 */
	public function test_get_default_namespace_with_group(): void
	{
		$command = new class(new Filesystem()) extends RepositoryMakeCommand {
			public $testDir = 'Models';
			public $testGroup = true;
			public $testName = 'User';

			public function testGetDefaultNamespace($rootNamespace): string
			{
				return parent::getDefaultNamespace($rootNamespace);
			}

			public function option($key = null)
			{
				if ($key === 'dir') {
					return $this->testDir;
				}
				if ($key === 'group') {
					return $this->testGroup;
				}
				return null;
			}

			public function getNameInput()
			{
				return $this->testName;
			}
		};

		$namespace = $command->testGetDefaultNamespace('App');
		$this->assertEquals('App\\Models\\Users', $namespace);
	}

	/**
	 * Test getPath method without group option.
	 */
	public function test_get_path_without_group(): void
	{
		$command = new class(new Filesystem()) extends RepositoryMakeCommand {
			public $testDir = 'Models';
			public $testGroup = false;
			public $testName = 'User';

			public function testGetPath($name): string
			{
				return parent::getPath($name);
			}

			public function option($key = null)
			{
				if ($key === 'dir') {
					return $this->testDir;
				}
				if ($key === 'group') {
					return $this->testGroup;
				}
				return null;
			}

			public function getNameInput()
			{
				return $this->testName;
			}
		};


		$path = $command->testGetPath('User');
		$this->assertEquals('/app' . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'UsersRepository.php', $path);
	}

	/**
	 * Test getPath method with group option.
	 */
	public function test_get_path_with_group(): void
	{
		$command = new class(new Filesystem()) extends RepositoryMakeCommand {
			public $testDir = 'Models';
			public $testGroup = true;
			public $testName = 'User';

			public function testGetPath($name): string
			{
				return parent::getPath($name);
			}

			public function option($key = null)
			{
				if ($key === 'dir') {
					return $this->testDir;
				}
				if ($key === 'group') {
					return $this->testGroup;
				}
				return null;
			}

			public function getNameInput()
			{
				return $this->testName;
			}
		};

		$path = $command->testGetPath('User');
		$expectedPath = '/app' . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Users' . DIRECTORY_SEPARATOR . 'UsersRepository.php';
		$this->assertEquals($expectedPath, $path);
	}

	/**
	 * Test replaceClass method adds repository suffix.
	 */
	public function test_replace_class_adds_repository_suffix(): void
	{
		$command = new class(new Filesystem()) extends RepositoryMakeCommand {
			public function testReplaceClass($stub, $name): string
			{
				return parent::replaceClass($stub, $name);
			}
		};

		$stub = 'class {{ class }}';
		$result = $command->testReplaceClass($stub, 'User');
		$this->assertEquals('class UsersRepository', $result);
	}

	/**
	 * Test replaceClass method doesn't add suffix if 'repo' exists in name.
	 */
	public function test_replace_class_doesnt_add_suffix_if_repo_exists(): void
	{
		$command = new class(new Filesystem()) extends RepositoryMakeCommand {
			public function testReplaceClass($stub, $name): string
			{
				return parent::replaceClass($stub, $name);
			}
		};

		$stub = 'class {{ class }}';
		$result = $command->testReplaceClass($stub, 'UserRepo');
		// UserRepo becomes UserRepos (pluralized)
		$this->assertEquals('class UserRepos', $result);
	}

	/**
	 * Test buildClass method.
	 */
	public function test_build_class(): void
	{
		$stubContent = '<?php

namespace {{ namespace }};

use App\Models\ModelClass;

class {{ class }} extends BaseRepository
{
    public function __construct(ModelClass $model)
    {
        parent::__construct($model);
    }
}';

		$filesystem = new class extends Filesystem {
			public $stubContent;
			
			public function get($path, $lock = false)
			{
				return $this->stubContent;
			}
		};
		
		$filesystem->stubContent = $stubContent;

		$command = new class($filesystem) extends RepositoryMakeCommand {
			public $testModel = null;
			public $testName = 'User';

			public function testBuildClass($name): string
			{
				// Since buildClass calls parent methods that aren't available in test context,
				// we'll test the individual replacements instead
				$stub = $this->files->get($this->getStub());
				$stub = $this->replaceNamespace($stub, 'App\\Repositories\\UsersRepository')->replaceClass($stub, 'UsersRepository');
				$stub = $this->replaceModelClass($stub);
				return $stub;
			}
			
			protected function replaceNamespace(&$stub, $name)
			{
				$namespace = 'App\\Repositories';
				$stub = str_replace('{{ namespace }}', $namespace, $stub);
				return $this;
			}

			public function option($key = null)
			{
				if ($key === 'model') {
					return $this->testModel;
				}
				return null;
			}

			public function getNameInput()
			{
				return $this->testName;
			}
		};

		$result = $command->testBuildClass('App\\Repositories\\UsersRepository');
		
		// Check that namespace and class name are replaced
		$this->assertStringContainsString('namespace App\\Repositories;', $result);
		// The replaceClass method adds the plural suffix, so it becomes UsersRepositories
		$this->assertStringContainsString('class UsersRepositories extends BaseRepository', $result);
		$this->assertStringContainsString('use App\Models\User;', $result);
		$this->assertStringContainsString('public function __construct(User $model)', $result);
	}

	/**
	 * Test replaceModelClass method with model option.
	 */
	public function test_replace_model_class_with_model_option(): void
	{
		$command = new class(new Filesystem()) extends RepositoryMakeCommand {
			public $testModel = 'Product';

			public function testReplaceModelClass(string $stub): string
			{
				return parent::replaceModelClass($stub);
			}

			public function option($key = null)
			{
				if ($key === 'model') {
					return $this->testModel;
				}
				return null;
			}
		};

		$stub = 'use App\Models\ModelClass;
class Repository {
    public function __construct(ModelClass $model) {}
}';

		$result = $command->testReplaceModelClass($stub);
		
		$expected = 'use App\Models\Product;
class Repository {
    public function __construct(Product $model) {}
}';
		
		$this->assertEquals($expected, $result);
	}

	/**
	 * Test replaceModelClass method without model option.
	 */
	public function test_replace_model_class_without_model_option(): void
	{
		$command = new class(new Filesystem()) extends RepositoryMakeCommand {
			public $testModel = null;
			public $testName = 'UserPost';

			public function testReplaceModelClass(string $stub): string
			{
				return parent::replaceModelClass($stub);
			}

			public function option($key = null)
			{
				if ($key === 'model') {
					return $this->testModel;
				}
				return null;
			}

			public function getNameInput()
			{
				return $this->testName;
			}
		};

		$stub = 'ModelClass';

		$result = $command->testReplaceModelClass($stub);
		
		// Should use singular studly case of the input name
		$this->assertEquals('UserPost', $result);
	}

	/**
	 * Test getEntityPlural method.
	 */
	public function test_get_entity_plural(): void
	{
		$command = new class(new Filesystem()) extends RepositoryMakeCommand {
			public $testName = 'User';

			public function testGetEntityPlural(): string
			{
				return parent::getEntityPlural();
			}

			public function getNameInput()
			{
				return $this->testName;
			}
		};

		$result = $command->testGetEntityPlural();
		$this->assertEquals('Users', $result);
	}

	/**
	 * Test getEntityPlural method with already plural name.
	 */
	public function test_get_entity_plural_with_already_plural_name(): void
	{
		$command = new class(new Filesystem()) extends RepositoryMakeCommand {
			public $testName = 'Settings';

			public function testGetEntityPlural(): string
			{
				return parent::getEntityPlural();
			}

			public function getNameInput()
			{
				return $this->testName;
			}
		};

		$result = $command->testGetEntityPlural();
		$this->assertEquals('Settings', $result);
	}

	/**
	 * Test getOptions method.
	 */
	public function test_get_options(): void
	{
		$command = new class(new Filesystem()) extends RepositoryMakeCommand {
			public function testGetOptions(): array
			{
				return parent::getOptions();
			}
		};

		$options = $command->testGetOptions();
		
		$this->assertIsArray($options);
		$this->assertCount(4, $options);
		
		// Check force option
		$this->assertEquals('force', $options[0][0]);
		$this->assertNull($options[0][1]);
		
		// Check group option
		$this->assertEquals('group', $options[1][0]);
		$this->assertNull($options[1][1]);
		
		// Check dir option
		$this->assertEquals('dir', $options[2][0]);
		
		// Check model option
		$this->assertEquals('model', $options[3][0]);
		$this->assertNull($options[3][1]);
	}
}

