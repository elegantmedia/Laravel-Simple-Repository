<?php

declare(strict_types=1);

namespace ElegantMedia\SimpleRepository\Tests\Unit\Config;

use ElegantMedia\SimpleRepository\Tests\TestCase;

class ConfigurationTest extends TestCase
{
	public function test_configuration_file_exists(): void
	{
		$configPath = __DIR__ . '/../../../config/simple-repository.php';
		$this->assertFileExists($configPath);
	}

	public function test_configuration_has_required_keys(): void
	{
		$config = require __DIR__ . '/../../../config/simple-repository.php';

		$this->assertIsArray($config);

		// Check main sections
		$this->assertArrayHasKey('defaults', $config);
		$this->assertArrayHasKey('search', $config);
		$this->assertArrayHasKey('command', $config);

		// Check defaults section
		$this->assertArrayHasKey('pagination', $config['defaults']);
		$this->assertArrayHasKey('sorting', $config['defaults']);

		// Check pagination defaults
		$this->assertArrayHasKey('per_page', $config['defaults']['pagination']);
		$this->assertArrayHasKey('max_per_page', $config['defaults']['pagination']);
		$this->assertEquals(50, $config['defaults']['pagination']['per_page']);
		$this->assertEquals(100, $config['defaults']['pagination']['max_per_page']);

		// Check sorting defaults
		$this->assertArrayHasKey('field', $config['defaults']['sorting']);
		$this->assertArrayHasKey('direction', $config['defaults']['sorting']);
		$this->assertEquals('created_at', $config['defaults']['sorting']['field']);
		$this->assertEquals('desc', $config['defaults']['sorting']['direction']);

		// Check search settings
		$this->assertArrayHasKey('query_parameter', $config['search']);
		$this->assertArrayHasKey('case_sensitive', $config['search']);
		$this->assertEquals('q', $config['search']['query_parameter']);
		$this->assertFalse($config['search']['case_sensitive']);

		// Check command settings
		$this->assertArrayHasKey('directory', $config['command']);
		$this->assertArrayHasKey('suffix', $config['command']);
		$this->assertEquals('Models', $config['command']['directory']);
		$this->assertEquals('Repository', $config['command']['suffix']);
	}
}
