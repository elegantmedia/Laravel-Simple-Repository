<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Create Cars Table Migration
 *
 * This migration creates the cars table for our example application
 */
return new class () extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('cars', function (Blueprint $table) {
			$table->id();

			// Basic car information
			$table->string('make', 50)->index();
			$table->string('model', 50)->index();
			$table->unsignedSmallInteger('year')->index();
			$table->string('color', 30);
			$table->decimal('price', 10, 2)->index();
			$table->string('vin', 17)->unique();

			// Status - using enum for better performance
			$table->enum('status', ['available', 'sold', 'reserved', 'maintenance'])
				  ->default('available')
				  ->index();

			// Additional information
			$table->text('description')->nullable();

			// Metadata
			$table->unsignedInteger('view_count')->default(0);
			$table->unsignedInteger('inquiry_count')->default(0);

			// Timestamps
			$table->timestamps();
			$table->softDeletes();

			// Composite indexes for common queries
			$table->index(['status', 'price']);
			$table->index(['make', 'model']);
			$table->index(['year', 'price']);
			$table->index(['created_at', 'status']);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('cars');
	}
};
