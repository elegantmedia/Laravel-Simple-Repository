<?php

declare(strict_types=1);

namespace ElegantMedia\SimpleRepository\Search\Contracts;

use Carbon\CarbonInterface;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;

interface DateFilterInterface
{
	/**
	 * Filter records where the date column equals the given date.
	 *
	 * @param CarbonInterface $date   The date to filter by
	 * @param string          $column The column to filter on
	 *
	 * @return self
	 */
	public function whereDateIs(CarbonInterface $date, string $column = 'created_at'): self;

	/**
	 * Filter records where the datetime column equals the given datetime.
	 *
	 * @param CarbonInterface $dateTime The datetime to filter by
	 * @param string          $column   The column to filter on
	 *
	 * @return self
	 */
	public function whereDateTimeIs(CarbonInterface $dateTime, string $column = 'created_at'): self;

	/**
	 * Filter records where the date column is before the given date.
	 *
	 * @param CarbonInterface $date   The date to compare against
	 * @param string          $column The column to filter on
	 *
	 * @return self
	 */
	public function whereDateBefore(CarbonInterface $date, string $column = 'created_at'): self;

	/**
	 * Filter records where the date column is after the given date.
	 *
	 * @param CarbonInterface $date   The date to compare against
	 * @param string          $column The column to filter on
	 *
	 * @return self
	 */
	public function whereDateAfter(CarbonInterface $date, string $column = 'created_at'): self;

	/**
	 * Filter records where the date column is between two dates (inclusive).
	 *
	 * @param CarbonInterface $start  The start date
	 * @param CarbonInterface $end    The end date
	 * @param string          $column The column to filter on
	 *
	 * @return self
	 */
	public function whereDateBetween(CarbonInterface $start, CarbonInterface $end, string $column = 'created_at'): self;

	/**
	 * Filter records where the date column is within the given period.
	 *
	 * @param CarbonPeriod $period The Carbon period to filter within
	 * @param string       $column The column to filter on
	 *
	 * @return self
	 */
	public function whereDateInPeriod(CarbonPeriod $period, string $column = 'created_at'): self;

	/**
	 * Filter records where the date column is within the given interval from now.
	 *
	 * @param CarbonInterval $interval The Carbon interval from now
	 * @param string         $column   The column to filter on
	 *
	 * @return self
	 */
	public function whereDateWithin(CarbonInterval $interval, string $column = 'created_at'): self;

	/**
	 * Filter records created today.
	 *
	 * @param string $column The column to filter on
	 *
	 * @return self
	 */
	public function whereDateToday(string $column = 'created_at'): self;

	/**
	 * Filter records created yesterday.
	 *
	 * @param string $column The column to filter on
	 *
	 * @return self
	 */
	public function whereDateYesterday(string $column = 'created_at'): self;

	/**
	 * Filter records created this week.
	 *
	 * @param string $column The column to filter on
	 *
	 * @return self
	 */
	public function whereDateThisWeek(string $column = 'created_at'): self;

	/**
	 * Filter records created last week.
	 *
	 * @param string $column The column to filter on
	 *
	 * @return self
	 */
	public function whereDateLastWeek(string $column = 'created_at'): self;

	/**
	 * Filter records created this month.
	 *
	 * @param string $column The column to filter on
	 *
	 * @return self
	 */
	public function whereDateThisMonth(string $column = 'created_at'): self;

	/**
	 * Filter records created last month.
	 *
	 * @param string $column The column to filter on
	 *
	 * @return self
	 */
	public function whereDateLastMonth(string $column = 'created_at'): self;

	/**
	 * Filter records created this year.
	 *
	 * @param string $column The column to filter on
	 *
	 * @return self
	 */
	public function whereDateThisYear(string $column = 'created_at'): self;

	/**
	 * Filter records created last year.
	 *
	 * @param string $column The column to filter on
	 *
	 * @return self
	 */
	public function whereDateLastYear(string $column = 'created_at'): self;

	/**
	 * Filter records created in the last N days.
	 *
	 * @param int    $days   Number of days to go back
	 * @param string $column The column to filter on
	 *
	 * @return self
	 */
	public function whereDateLastDays(int $days, string $column = 'created_at'): self;

	/**
	 * Filter records created in the last N hours.
	 *
	 * @param int    $hours  Number of hours to go back
	 * @param string $column The column to filter on
	 *
	 * @return self
	 */
	public function whereDateLastHours(int $hours, string $column = 'created_at'): self;

	/**
	 * Filter records created in the last N minutes.
	 *
	 * @param int    $minutes Number of minutes to go back
	 * @param string $column  The column to filter on
	 *
	 * @return self
	 */
	public function whereDateLastMinutes(int $minutes, string $column = 'created_at'): self;
}
