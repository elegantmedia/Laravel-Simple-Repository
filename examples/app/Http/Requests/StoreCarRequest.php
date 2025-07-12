<?php

declare(strict_types=1);

namespace Examples\App\Http\Requests;

use Examples\App\Models\Car;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Store Car Request.
 *
 * Validates data for creating a new car
 */
class StoreCarRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool
	{
		// In a real application, you would check permissions here
		// return $this->user()->can('create', Car::class);
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
	 */
	public function rules(): array
	{
		return [
			'make' => [
				'required',
				'string',
				'max:50',
			],
			'model' => [
				'required',
				'string',
				'max:50',
			],
			'year' => [
				'required',
				'integer',
				'min:1900',
				'max:' . (date('Y') + 1),
			],
			'color' => [
				'required',
				'string',
				'max:30',
			],
			'price' => [
				'required',
				'numeric',
				'min:0',
				'max:9999999.99',
			],
			'vin' => [
				'required',
				'string',
				'size:17',
				'unique:cars,vin',
				'regex:/^[A-HJ-NPR-Z0-9]{17}$/',
			],
			'status' => [
				'required',
				Rule::in(array_keys(Car::getStatuses())),
			],
			'description' => [
				'nullable',
				'string',
				'max:1000',
			],
		];
	}

	/**
	 * Get custom messages for validator errors.
	 *
	 * @return array<string, string>
	 */
	public function messages(): array
	{
		return [
			'vin.regex' => 'The VIN must be a valid 17-character vehicle identification number.',
			'vin.size' => 'The VIN must be exactly 17 characters.',
			'vin.unique' => 'This VIN is already registered in the system.',
			'year.max' => 'The year cannot be more than one year in the future.',
			'price.max' => 'The price cannot exceed $9,999,999.99.',
		];
	}

	/**
	 * Prepare the data for validation.
	 */
	protected function prepareForValidation(): void
	{
		// Convert VIN to uppercase
		if ($this->has('vin')) {
			$this->merge([
				'vin' => strtoupper($this->vin),
			]);
		}

		// Trim string inputs
		$this->merge([
			'make' => trim($this->make ?? ''),
			'model' => trim($this->model ?? ''),
			'color' => trim($this->color ?? ''),
		]);
	}
}
