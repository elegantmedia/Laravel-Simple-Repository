<?php

declare(strict_types=1);

namespace ElegantMedia\SimpleRepository\Tests\Fixtures\Repositories;

use ElegantMedia\SimpleRepository\Repository\BaseRepository;
use ElegantMedia\SimpleRepository\Tests\Fixtures\Models\TestModel;

class TestRepository extends BaseRepository
{
	public function __construct()
	{
		parent::__construct(new TestModel());
	}
}
