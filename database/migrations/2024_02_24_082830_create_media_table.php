<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('media', function (Blueprint $table) {
      $table->id();
      $table->string('url')->unique();
      $table->string('path')->nullable();
      $table->string('is_loaded')->default(false);

      $table->timestamp('last_used_at')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('media');
  }
};
