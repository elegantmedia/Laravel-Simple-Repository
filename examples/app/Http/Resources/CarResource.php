<?php

declare(strict_types=1);

namespace Examples\App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Car Resource.
 *
 * Transforms a Car model into a JSON representation for API responses.
 * This resource controls what car data is exposed through the API.
 */
class CarResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'make' => $this->make,
			'model' => $this->model,
			'full_name' => $this->full_name, // Accessor from model
			'year' => $this->year,
			'color' => $this->color,
			'price' => $this->price,
			'formatted_price' => $this->formatted_price, // Accessor from model
			'vin' => $this->when(
				// Only show VIN to authenticated users or admins
				$request->user() && ($request->user()->isAdmin() || $request->user()->id === $this->owner_id),
				$this->vin
			),
			'status' => $this->status,
			'status_label' => $this->getStatuses()[$this->status] ?? 'Unknown',
			'description' => $this->description,
			'is_available' => $this->status === 'available',
			'created_at' => $this->created_at->toIso8601String(),
			'updated_at' => $this->updated_at->toIso8601String(),

			// Include relationships when loaded
			'owner' => UserResource::make($this->whenLoaded('owner')),
			'images' => CarImageResource::collection($this->whenLoaded('images')),

			// Include additional data based on request
			$this->mergeWhen($request->input('include_stats'), [
				'view_count' => $this->view_count ?? 0,
				'inquiry_count' => $this->inquiry_count ?? 0,
				'days_on_market' => $this->created_at->diffInDays(now()),
			]),

			// Links
			'links' => [
				'self' => route('api.cars.show', $this->id),
				'web' => route('cars.show', $this->id),
			],
		];
	}

	/**
	 * Get additional data that should be returned with the resource array.
	 *
	 * @param Request $request
	 *
	 * @return array<string, mixed>
	 */
	public function with(Request $request): array
	{
		return [
			'meta' => [
				'api_version' => 'v1',
				'response_time' => round((microtime(true) - LARAVEL_START) * 1000, 2) . 'ms',
			],
		];
	}
}
