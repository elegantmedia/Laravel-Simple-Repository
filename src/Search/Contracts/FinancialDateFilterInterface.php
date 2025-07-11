<?php

declare(strict_types=1);

namespace ElegantMedia\SimpleRepository\Search\Contracts;

interface FinancialDateFilterInterface
{
	/**
	 * Filter records created this quarter.
	 *
	 * @param string $column The column to filter on
	 *
	 * @return self
	 */
	public function whereDateThisQuarter(string $column = 'created_at'): self;

	/**
	 * Filter records created last quarter.
	 *
	 * @param string $column The column to filter on
	 *
	 * @return self
	 */
	public function whereDateLastQuarter(string $column = 'created_at'): self;

	/**
	 * Filter records created in a specific quarter.
	 *
	 * @param int      $quarterNumber The quarter number (1-4)
	 * @param int|null $year          The year (defaults to current year)
	 * @param string   $column        The column to filter on
	 *
	 * @return self
	 */
	public function whereDateInQuarter(int $quarterNumber, ?int $year = null, string $column = 'created_at'): self;

	/**
	 * Filter records created in the current financial year.
	 * Note: Financial year is assumed to end on June 30th (July 1st - June 30th).
	 *
	 * @param string $column The column to filter on
	 *
	 * @return self
	 */
	public function whereDateInThisFinancialYear(string $column = 'created_at'): self;

	/**
	 * Filter records created in the last financial year.
	 * Note: Financial year is assumed to end on June 30th (July 1st - June 30th).
	 *
	 * @param string $column The column to filter on
	 *
	 * @return self
	 */
	public function whereDateInLastFinancialYear(string $column = 'created_at'): self;

	/**
	 * Filter records created in a specific financial year.
	 * Note: Financial year is assumed to end on June 30th (July 1st - June 30th).
	 *
	 * @param int    $endingYear The year when the financial year ends (e.g., 2024 for FY 2023-2024)
	 * @param string $column     The column to filter on
	 *
	 * @return self
	 */
	public function whereDateInFinancialYear(int $endingYear, string $column = 'created_at'): self;
}
