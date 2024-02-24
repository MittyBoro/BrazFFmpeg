<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('tasks', function (Blueprint $table) {
      $table->id();

      $table->string('type')->nullable();
      $table->string('status')->nullable();
      $table->string('webhook_url')->nullable();

      $table
        ->foreignId('media_id')
        ->nullable()
        ->constrained('media')
        ->nullOnDelete();

      $table->unsignedInteger('progress')->default(0);
      $table->unsignedInteger('duration')->default(0);

      $table->json('data')->default(new Expression('(JSON_ARRAY())'));
      $table->json('result')->default(new Expression('(JSON_ARRAY())'));

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('tasks');
  }
};
