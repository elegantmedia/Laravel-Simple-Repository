<?php

declare(strict_types=1);

namespace ElegantMedia\SimpleRepository\Search\Traits;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use ElegantMedia\SimpleRepository\Exceptions\InvalidDateRangeException;

trait DateFilterableTrait
{
	/**
	 * Filter records where the date column equals the given date.
	 */
	public function whereDateIs(CarbonInterface $date, string $column = 'created_at'): self
	{
		$this->conditions[] = [
			'type' => 'whereDate',
			'args' => [$column, $date->toDateString()],
		];

		return $this;
	}

	/**
	 * Filter records where the datetime column equals the given datetime.
	 */
	public function whereDateTimeIs(CarbonInterface $dateTime, string $column = 'created_at'): self
	{
		$this->conditions[] = [
			'type' => 'where',
			'args' => [$column, '=', $dateTime->toDateTimeString()],
		];

		return $this;
	}

	/**
	 * Filter records where the date column is before the given date.
	 */
	public function whereDateBefore(CarbonInterface $date, string $column = 'created_at'): self
	{
		$this->conditions[] = [
			'type' => 'where',
			'args' => [$column, '<', $date->startOfDay()],
		];

		return $this;
	}

	/**
	 * Filter records where the date column is after the given date.
	 */
	public function whereDateAfter(CarbonInterface $date, string $column = 'created_at'): self
	{
		$this->conditions[] = [
			'type' => 'where',
			'args' => [$column, '>', $date->endOfDay()],
		];

		return $this;
	}

	/**
	 * Filter records where the date column is between two dates (inclusive).
	 */
	public function whereDateBetween(CarbonInterface $start, CarbonInterface $end, string $column = 'created_at'): self
	{
		// Validate date range
		if ($start->isAfter($end)) {
			throw InvalidDateRangeException::startDateAfterEndDate(
				$start->toDateString(),
				$end->toDateString(),
				$column
			);
		}

		$this->conditions[] = [
			'type' => 'whereBetween',
			'args' => [$column, [$start->startOfDay(), $end->endOfDay()]],
		];

		return $this;
	}

	/**
	 * Filter records where the date column is within the given period.
	 */
	public function whereDateInPeriod(CarbonPeriod $period, string $column = 'created_at'): self
	{
		// Get start and end dates from the period
		$start = $period->getStartDate();
		$end = $period->getEndDate();

		if ($end === null) {
			throw InvalidDateRangeException::nullDatesInRange($column);
		}

		return $this->whereDateBetween($start, $end, $column);
	}

	/**
	 * Filter records where the date column is within the given interval from now.
	 */
	public function whereDateWithin(CarbonInterval $interval, string $column = 'created_at'): self
	{
		$now = Carbon::now();
		$past = $now->copy()->sub($interval);

		return $this->whereDateBetween($past, $now, $column);
	}

	/**
	 * Filter records created today.
	 */
	public function whereDateToday(string $column = 'created_at'): self
	{
		$today = Carbon::today();

		$this->conditions[] = [
			'type' => 'whereBetween',
			'args' => [$column, [$today->startOfDay(), $today->endOfDay()]],
		];

		return $this;
	}

	/**
	 * Filter records created yesterday.
	 */
	public function whereDateYesterday(string $column = 'created_at'): self
	{
		$yesterday = Carbon::yesterday();

		$this->conditions[] = [
			'type' => 'whereBetween',
			'args' => [$column, [$yesterday->startOfDay(), $yesterday->endOfDay()]],
		];

		return $this;
	}

	/**
	 * Filter records created this week.
	 */
	public function whereDateThisWeek(string $column = 'created_at'): self
	{
		$startOfWeek = Carbon::now()->startOfWeek();
		$endOfWeek = Carbon::now()->endOfWeek();

		return $this->whereDateBetween($startOfWeek, $endOfWeek, $column);
	}

	/**
	 * Filter records created last week.
	 */
	public function whereDateLastWeek(string $column = 'created_at'): self
	{
		$startOfLastWeek = Carbon::now()->subWeek()->startOfWeek();
		$endOfLastWeek = Carbon::now()->subWeek()->endOfWeek();

		return $this->whereDateBetween($startOfLastWeek, $endOfLastWeek, $column);
	}

	/**
	 * Filter records created this month.
	 */
	public function whereDateThisMonth(string $column = 'created_at'): self
	{
		$startOfMonth = Carbon::now()->startOfMonth();
		$endOfMonth = Carbon::now()->endOfMonth();

		return $this->whereDateBetween($startOfMonth, $endOfMonth, $column);
	}

	/**
	 * Filter records created last month.
	 */
	public function whereDateLastMonth(string $column = 'created_at'): self
	{
		$startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
		$endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();

		return $this->whereDateBetween($startOfLastMonth, $endOfLastMonth, $column);
	}

	/**
	 * Filter records created this year.
	 */
	public function whereDateThisYear(string $column = 'created_at'): self
	{
		$startOfYear = Carbon::now()->startOfYear();
		$endOfYear = Carbon::now()->endOfYear();

		return $this->whereDateBetween($startOfYear, $endOfYear, $column);
	}

	/**
	 * Filter records created last year.
	 */
	public function whereDateLastYear(string $column = 'created_at'): self
	{
		$startOfLastYear = Carbon::now()->subYear()->startOfYear();
		$endOfLastYear = Carbon::now()->subYear()->endOfYear();

		return $this->whereDateBetween($startOfLastYear, $endOfLastYear, $column);
	}

	/**
	 * Filter records created in the last N days.
	 */
	public function whereDateLastDays(int $days, string $column = 'created_at'): self
	{
		$start = Carbon::now()->subDays($days)->startOfDay();
		$end = Carbon::now()->endOfDay();

		return $this->whereDateBetween($start, $end, $column);
	}

	/**
	 * Filter records created in the last N hours.
	 */
	public function whereDateLastHours(int $hours, string $column = 'created_at'): self
	{
		$start = Carbon::now()->subHours($hours);
		$end = Carbon::now();

		$this->conditions[] = [
			'type' => 'whereBetween',
			'args' => [$column, [$start, $end]],
		];

		return $this;
	}

	/**
	 * Filter records created in the last N minutes.
	 */
	public function whereDateLastMinutes(int $minutes, string $column = 'created_at'): self
	{
		$start = Carbon::now()->subMinutes($minutes);
		$end = Carbon::now();

		$this->conditions[] = [
			'type' => 'whereBetween',
			'args' => [$column, [$start, $end]],
		];

		return $this;
	}
}
