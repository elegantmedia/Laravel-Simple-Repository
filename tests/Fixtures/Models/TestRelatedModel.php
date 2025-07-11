<?php

declare(strict_types=1);

namespace ElegantMedia\SimpleRepository\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;

class TestRelatedModel extends Model
{
	protected $table = 'test_related_models';

	protected $fillable = [
		'test_model_id',
		'name',
		'status',
	];

	protected $casts = [
		'test_model_id' => 'integer',
	];

	/**
	 * Get the parent test model.
	 */
	public function testModel()
	{
		return $this->belongsTo(TestModel::class);
	}
}
