<?php

declare(strict_types=1);

namespace ElegantMedia\SimpleRepository\Tests\Unit\Core\Utilities;

use ElegantMedia\SimpleRepository\Exceptions\InvalidArgumentException;
use ElegantMedia\SimpleRepository\Exceptions\InvalidDateRangeException;
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

	public function test_invalid_date_range_start_date_after_end_date(): void
	{
		$exception = InvalidDateRangeException::startDateAfterEndDate('2023-12-31', '2023-01-01', 'created_at');

		$this->assertInstanceOf(InvalidDateRangeException::class, $exception);
		$this->assertInstanceOf(RepositoryException::class, $exception);
		$this->assertEquals(
			"Invalid date range for column 'created_at': Start date (2023-12-31) cannot be after end date (2023-01-01).",
			$exception->getMessage()
		);
	}

	public function test_invalid_date_range_start_date_equals_end_date(): void
	{
		$exception = InvalidDateRangeException::startDateEqualsEndDate('2023-06-15', 'updated_at');

		$this->assertInstanceOf(InvalidDateRangeException::class, $exception);
		$this->assertInstanceOf(RepositoryException::class, $exception);
		$this->assertEquals(
			"Invalid date range for column 'updated_at': Start date and end date cannot be the same (2023-06-15).",
			$exception->getMessage()
		);
	}

	public function test_invalid_date_range_null_dates_in_range(): void
	{
		$exception = InvalidDateRangeException::nullDatesInRange('published_at');

		$this->assertInstanceOf(InvalidDateRangeException::class, $exception);
		$this->assertInstanceOf(RepositoryException::class, $exception);
		$this->assertEquals(
			"Invalid date range for column 'published_at': Both start and end dates must be provided.",
			$exception->getMessage()
		);
	}

	public function test_invalid_date_range_invalid_quarter_number(): void
	{
		$exception = InvalidDateRangeException::invalidQuarterNumber(5);

		$this->assertInstanceOf(InvalidDateRangeException::class, $exception);
		$this->assertInstanceOf(RepositoryException::class, $exception);
		$this->assertEquals(
			"Invalid quarter number: 5. Quarter must be between 1 and 4.",
			$exception->getMessage()
		);
	}

	public function test_invalid_date_range_invalid_year(): void
	{
		$exception = InvalidDateRangeException::invalidYear(1850);

		$this->assertInstanceOf(InvalidDateRangeException::class, $exception);
		$this->assertInstanceOf(RepositoryException::class, $exception);
		$this->assertEquals(
			"Invalid year: 1850. Year must be a reasonable value (between 1900 and 2100).",
			$exception->getMessage()
		);
	}
}
