<?php

declare(strict_types=1);

namespace Examples\Database\Seeders;

use Examples\App\Models\Car;
use Illuminate\Database\Seeder;

/**
 * Car Seeder.
 *
 * Seeds the database with sample car data for testing
 */
class CarSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		// Create 100 random cars
		Car::factory()->count(100)->create();

		// Create 20 luxury cars
		Car::factory()->luxury()->count(20)->create();

		// Create 30 affordable cars
		Car::factory()->affordable()->count(30)->create();

		// Create some specific cars for testing
		Car::factory()->create([
			'make' => 'Toyota',
			'model' => 'Camry',
			'year' => 2024,
			'color' => 'Silver',
			'price' => 35000,
			'status' => 'available',
			'description' => 'Brand new 2024 Toyota Camry with advanced safety features and excellent fuel economy.',
		]);

		Car::factory()->create([
			'make' => 'Honda',
			'model' => 'Civic',
			'year' => 2023,
			'color' => 'Blue',
			'price' => 28000,
			'status' => 'available',
			'description' => 'Sporty and reliable Honda Civic with low mileage and full warranty.',
		]);

		Car::factory()->sold()->count(50)->create();

		$this->command->info('Cars table seeded with 200+ records!');
	}
}
