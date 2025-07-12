<?php

declare(strict_types=1);

namespace Examples\App\Models;

use ElegantMedia\SimpleRepository\Search\Traits\SearchableLike;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Car Model.
 *
 * Represents a vehicle in our application with searchable capabilities
 * using the Laravel Simple Repository package.
 *
 * @property int                 $id
 * @property string              $make
 * @property string              $model
 * @property int                 $year
 * @property string              $color
 * @property float               $price
 * @property string              $vin
 * @property string              $status
 * @property string|null         $description
 * @property \Carbon\Carbon      $created_at
 * @property \Carbon\Carbon      $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Car extends Model
{
	use HasFactory;
	use SoftDeletes;
	use SearchableLike;

	/**
	 * The table associated with the model.
	 */
	protected $table = 'cars';

	/**
	 * The attributes that are mass assignable.
	 */
	protected $fillable = [
		'make',
		'model',
		'year',
		'color',
		'price',
		'vin',
		'status',
		'description',
	];

	/**
	 * The attributes that should be cast.
	 */
	protected $casts = [
		'year' => 'integer',
		'price' => 'float',
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
		'deleted_at' => 'datetime',
	];

	/**
	 * The attributes that should be searchable.
	 * These fields will be searched when using the repository's search methods.
	 */
	protected array $searchable = [
		'make',
		'model',
		'vin',
		'color',
		'description',
	];

	/**
	 * Available status values.
	 */
	public const STATUS_AVAILABLE = 'available';
	public const STATUS_SOLD = 'sold';
	public const STATUS_RESERVED = 'reserved';
	public const STATUS_MAINTENANCE = 'maintenance';

	/**
	 * Get all available statuses.
	 */
	public static function getStatuses(): array
	{
		return [
			self::STATUS_AVAILABLE => 'Available',
			self::STATUS_SOLD => 'Sold',
			self::STATUS_RESERVED => 'Reserved',
			self::STATUS_MAINTENANCE => 'Under Maintenance',
		];
	}

	/**
	 * Scope to filter available cars.
	 */
	public function scopeAvailable($query)
	{
		return $query->where('status', self::STATUS_AVAILABLE);
	}

	/**
	 * Scope to filter cars by price range.
	 */
	public function scopePriceRange($query, float $min, float $max)
	{
		return $query->whereBetween('price', [$min, $max]);
	}

	/**
	 * Get the full name of the car (make and model).
	 */
	public function getFullNameAttribute(): string
	{
		return "{$this->make} {$this->model}";
	}

	/**
	 * Get formatted price.
	 */
	public function getFormattedPriceAttribute(): string
	{
		return '$' . number_format($this->price, 2);
	}
}
