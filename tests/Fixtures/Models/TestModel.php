<?php

declare(strict_types=1);

namespace ElegantMedia\SimpleRepository\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestModel extends Model
{
	use HasFactory, SoftDeletes;

	protected $table = 'test_models';

	/**
	 * Fields that are searchable.
	 *
	 * @var array
	 */
	public $searchable = ['name', 'email', 'description'];

	protected $fillable = [
		'uuid',
		'name',
		'email',
		'status',
		'description',
		'price',
		'quantity',
		'is_active',
		'metadata',
		'published_at',
	];

	protected $casts = [
		'is_active' => 'boolean',
		'metadata' => 'array',
		'published_at' => 'datetime',
		'price' => 'decimal:2',
		'quantity' => 'integer',
	];

	protected $attributes = [
		'status' => 'active',
		'quantity' => 0,
		'is_active' => true,
	];

	/**
	 * Scope for searching by keyword.
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param string $keyword
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeSearchByKeyword($query, $keyword)
	{
		return $query->where(function ($q) use ($keyword) {
			$q->where('name', 'like', '%' . $keyword . '%')
			  ->orWhere('email', 'like', '%' . $keyword . '%')
			  ->orWhere('description', 'like', '%' . $keyword . '%');
		});
	}

	/**
	 * Get the related models.
	 */
	public function relatedModels()
	{
		return $this->hasMany(TestRelatedModel::class);
	}

	/**
	 * Get active related models.
	 */
	public function activeRelatedModels()
	{
		return $this->hasMany(TestRelatedModel::class)->where('status', 'active');
	}
}
