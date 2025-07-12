<?php

declare(strict_types=1);

namespace Examples\Database\Factories;

use Examples\App\Models\Car;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Car Factory.
 *
 * Generates fake car data for testing and seeding
 */
class CarFactory extends Factory
{
	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var class-string<\Illuminate\Database\Eloquent\Model>
	 */
	protected $model = Car::class;

	/**
	 * Popular car makes and their models.
	 */
	private array $makesAndModels = [
		'Toyota' => ['Camry', 'Corolla', 'RAV4', 'Highlander', 'Tacoma', 'Prius'],
		'Honda' => ['Civic', 'Accord', 'CR-V', 'Pilot', 'Odyssey', 'HR-V'],
		'Ford' => ['F-150', 'Mustang', 'Explorer', 'Escape', 'Edge', 'Ranger'],
		'Chevrolet' => ['Silverado', 'Malibu', 'Equinox', 'Tahoe', 'Camaro', 'Traverse'],
		'BMW' => ['3 Series', '5 Series', 'X3', 'X5', 'M3', 'M5'],
		'Mercedes-Benz' => ['C-Class', 'E-Class', 'GLE', 'GLC', 'S-Class', 'A-Class'],
		'Audi' => ['A4', 'A6', 'Q5', 'Q7', 'A3', 'Q3'],
		'Volkswagen' => ['Jetta', 'Passat', 'Tiguan', 'Atlas', 'Golf', 'ID.4'],
		'Nissan' => ['Altima', 'Sentra', 'Rogue', 'Pathfinder', 'Murano', 'Maxima'],
		'Tesla' => ['Model 3', 'Model Y', 'Model S', 'Model X'],
	];

	/**
	 * Car colors.
	 */
	private array $colors = [
		'Black', 'White', 'Silver', 'Gray', 'Red', 'Blue',
		'Green', 'Brown', 'Beige', 'Pearl White', 'Metallic Gray',
		'Midnight Blue', 'Cherry Red', 'Forest Green',
	];

	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		$make = $this->faker->randomElement(array_keys($this->makesAndModels));
		$model = $this->faker->randomElement($this->makesAndModels[$make]);
		$year = $this->faker->numberBetween(2015, date('Y') + 1);

		// Generate price based on make and year
		$basePrice = match($make) {
			'BMW', 'Mercedes-Benz', 'Audi', 'Tesla' => $this->faker->numberBetween(40000, 120000),
			'Toyota', 'Honda', 'Ford', 'Chevrolet' => $this->faker->numberBetween(20000, 60000),
			default => $this->faker->numberBetween(25000, 80000),
		};

		// Adjust price based on year (depreciation)
		$currentYear = date('Y');
		$age = $currentYear - $year;
		$price = $basePrice * (1 - ($age * 0.1)); // 10% depreciation per year

		return [
			'make' => $make,
			'model' => $model,
			'year' => $year,
			'color' => $this->faker->randomElement($this->colors),
			'price' => round($price, -2), // Round to nearest hundred
			'vin' => $this->generateVin(),
			'status' => $this->faker->randomElement(['available', 'available', 'available', 'sold', 'reserved']), // 60% available
			'description' => $this->generateDescription($make, $model, $year),
			'view_count' => $this->faker->numberBetween(0, 1000),
			'inquiry_count' => $this->faker->numberBetween(0, 50),
		];
	}

	/**
	 * Generate a valid VIN.
	 */
	private function generateVin(): string
	{
		// VIN characters (excluding I, O, Q)
		$chars = 'ABCDEFGHJKLMNPRSTUVWXYZ0123456789';
		$vin = '';

		for ($i = 0; $i < 17; $i++) {
			$vin .= $chars[rand(0, strlen($chars) - 1)];
		}

		return $vin;
	}

	/**
	 * Generate a realistic car description.
	 */
	private function generateDescription(string $make, string $model, int $year): string
	{
		$conditions = ['Excellent', 'Very Good', 'Good', 'Fair'];
		$condition = $this->faker->randomElement($conditions);

		$features = [
			'leather seats',
			'sunroof',
			'navigation system',
			'backup camera',
			'heated seats',
			'Bluetooth connectivity',
			'cruise control',
			'keyless entry',
			'alloy wheels',
			'premium sound system',
		];

		$selectedFeatures = $this->faker->randomElements($features, $this->faker->numberBetween(3, 6));

		return sprintf(
			'%d %s %s in %s condition. Features include: %s. %s',
			$year,
			$make,
			$model,
			strtolower($condition),
			implode(', ', $selectedFeatures),
			$this->faker->optional(0.5)->sentence() ?? ''
		);
	}

	/**
	 * Indicate that the car is available.
	 */
	public function available(): static
	{
		return $this->state(fn (array $attributes) => [
			'status' => 'available',
		]);
	}

	/**
	 * Indicate that the car is sold.
	 */
	public function sold(): static
	{
		return $this->state(fn (array $attributes) => [
			'status' => 'sold',
		]);
	}

	/**
	 * Indicate that the car is a luxury vehicle.
	 */
	public function luxury(): static
	{
		$luxuryMakes = ['BMW', 'Mercedes-Benz', 'Audi', 'Tesla'];
		$make = $this->faker->randomElement($luxuryMakes);

		return $this->state(fn (array $attributes) => [
			'make' => $make,
			'model' => $this->faker->randomElement($this->makesAndModels[$make]),
			'price' => $this->faker->numberBetween(60000, 150000),
		]);
	}

	/**
	 * Indicate that the car is affordable.
	 */
	public function affordable(): static
	{
		return $this->state(fn (array $attributes) => [
			'price' => $this->faker->numberBetween(10000, 30000),
			'year' => $this->faker->numberBetween(2010, 2018),
		]);
	}
}
