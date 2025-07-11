<?php

declare(strict_types=1);

namespace ElegantMedia\SimpleRepository\Search\Traits;

use Carbon\Carbon;
use ElegantMedia\SimpleRepository\Exceptions\InvalidDateRangeException;

trait FinancialDateFilterableTrait
{
	/**
	 * Filter records created this quarter.
	 */
	public function whereDateThisQuarter(string $column = 'created_at'): self
	{
		$now = Carbon::now();
		$quarter = $now->quarter;

		return $this->whereDateInQuarter($quarter, $now->year, $column);
	}

	/**
	 * Filter records created last quarter.
	 */
	public function whereDateLastQuarter(string $column = 'created_at'): self
	{
		$now = Carbon::now();
		$lastQuarter = $now->copy()->subQuarter();

		return $this->whereDateInQuarter($lastQuarter->quarter, $lastQuarter->year, $column);
	}

	/**
	 * Filter records created in a specific quarter.
	 */
	public function whereDateInQuarter(int $quarterNumber, ?int $year = null, string $column = 'created_at'): self
	{
		if ($quarterNumber < 1 || $quarterNumber > 4) {
			throw InvalidDateRangeException::invalidQuarterNumber($quarterNumber);
		}

		$year = $year ?? Carbon::now()->year;

		// Validate year is reasonable
		if ($year < 1900 || $year > 2100) {
			throw InvalidDateRangeException::invalidYear($year);
		}

		// Calculate quarter start and end dates
		$startMonth = ($quarterNumber - 1) * 3 + 1;
		$start = Carbon::create($year, $startMonth, 1)->startOfDay();
		$end = $start->copy()->endOfQuarter()->endOfDay();

		return $this->whereDateBetween($start, $end, $column);
	}

	/**
	 * Filter records created in the current financial year.
	 * Financial year runs from July 1st to June 30th.
	 */
	public function whereDateInThisFinancialYear(string $column = 'created_at'): self
	{
		$now = Carbon::now();
		$endingYear = $now->month >= 7 ? $now->year + 1 : $now->year;

		return $this->whereDateInFinancialYear($endingYear, $column);
	}

	/**
	 * Filter records created in the last financial year.
	 * Financial year runs from July 1st to June 30th.
	 */
	public function whereDateInLastFinancialYear(string $column = 'created_at'): self
	{
		$now = Carbon::now();
		$endingYear = $now->month >= 7 ? $now->year : $now->year - 1;

		return $this->whereDateInFinancialYear($endingYear, $column);
	}

	/**
	 * Filter records created in a specific financial year.
	 * Financial year runs from July 1st to June 30th.
	 */
	public function whereDateInFinancialYear(int $endingYear, string $column = 'created_at'): self
	{
		// Validate year is reasonable
		if ($endingYear < 1900 || $endingYear > 2100) {
			throw InvalidDateRangeException::invalidYear($endingYear);
		}

		// Financial year starts July 1st of the previous year
		$start = Carbon::create($endingYear - 1, 7, 1)->startOfDay();
		// Financial year ends June 30th of the ending year
		$end = Carbon::create($endingYear, 6, 30)->endOfDay();

		return $this->whereDateBetween($start, $end, $column);
	}
}
