<?php

declare(strict_types=1);

namespace ElegantMedia\SimpleRepository\Exceptions;

class InvalidDateRangeException extends RepositoryException
{
	/**
	 * Create exception for invalid date range.
	 *
	 * @param string $startDate
	 * @param string $endDate
	 * @param string $column
	 *
	 * @return self
	 */
	public static function startDateAfterEndDate(string $startDate, string $endDate, string $column): self
	{
		return new self(
			"Invalid date range for column '{$column}': Start date ({$startDate}) cannot be after end date ({$endDate})."
		);
	}

	/**
	 * Create exception for equal start and end dates when not allowed.
	 *
	 * @param string $date
	 * @param string $column
	 *
	 * @return self
	 */
	public static function startDateEqualsEndDate(string $date, string $column): self
	{
		return new self(
			"Invalid date range for column '{$column}': Start date and end date cannot be the same ({$date})."
		);
	}

	/**
	 * Create exception for null dates in range.
	 *
	 * @param string $column
	 *
	 * @return self
	 */
	public static function nullDatesInRange(string $column): self
	{
		return new self(
			"Invalid date range for column '{$column}': Both start and end dates must be provided."
		);
	}

	/**
	 * Create exception for invalid quarter number.
	 *
	 * @param int $quarterNumber
	 *
	 * @return self
	 */
	public static function invalidQuarterNumber(int $quarterNumber): self
	{
		return new self(
			"Invalid quarter number: {$quarterNumber}. Quarter must be between 1 and 4."
		);
	}

	/**
	 * Create exception for invalid year.
	 *
	 * @param int $year
	 *
	 * @return self
	 */
	public static function invalidYear(int $year): self
	{
		return new self(
			"Invalid year: {$year}. Year must be a reasonable value (between 1900 and 2100)."
		);
	}
}
