<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
	public function up(): void
	{
		Schema::create('test_models', function (Blueprint $table) {
			$table->id();
			$table->uuid('uuid')->unique()->nullable();
			$table->string('name');
			$table->string('email')->nullable();
			$table->string('status')->default('active');
			$table->text('description')->nullable();
			$table->decimal('price', 10, 2)->nullable();
			$table->integer('quantity')->default(0);
			$table->boolean('is_active')->default(true);
			$table->json('metadata')->nullable();
			$table->timestamp('published_at')->nullable();
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('test_models');
	}
};
