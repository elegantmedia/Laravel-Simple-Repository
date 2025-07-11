<?php

declare(strict_types=1);

namespace ElegantMedia\SimpleRepository\Tests\Unit;

use ElegantMedia\SimpleRepository\Exceptions\InvalidArgumentException;
use ElegantMedia\SimpleRepository\Exceptions\KeyNotFoundInAttributesException;
use ElegantMedia\SimpleRepository\Exceptions\RepositoryException;
use ElegantMedia\SimpleRepository\Exceptions\RepositoryExceptionInterface;
use ElegantMedia\SimpleRepository\Tests\TestCase;

class ExceptionTest extends TestCase
{
	public function test_key_not_found_exception_implements_interface(): void
	{
		$exception = new KeyNotFoundInAttributesException('Test message');

		$this->assertInstanceOf(RepositoryExceptionInterface::class, $exception);
		$this->assertInstanceOf(\Exception::class, $exception);
		$this->assertEquals('Test message', $exception->getMessage());
	}

	public function test_invalid_argument_exception_implements_interface(): void
	{
		$exception = new InvalidArgumentException('Invalid argument');

		$this->assertInstanceOf(RepositoryExceptionInterface::class, $exception);
		$this->assertInstanceOf(\InvalidArgumentException::class, $exception);
		$this->assertEquals('Invalid argument', $exception->getMessage());
	}

	public function test_repository_exception_implements_interface(): void
	{
		$exception = new RepositoryException('Repository error');

		$this->assertInstanceOf(RepositoryExceptionInterface::class, $exception);
		$this->assertInstanceOf(\Exception::class, $exception);
		$this->assertEquals('Repository error', $exception->getMessage());
	}
}
