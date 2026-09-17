<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('learning_tracks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->boolean('is_active')->default(false); // Default inactive so admin builds privately
            $table->boolean('is_default')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('track_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_track_id')->constrained('learning_tracks')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->integer('step_number')->default(1);
            $table->string('title_override')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['learning_track_id', 'step_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('track_units');
        Schema::dropIfExists('learning_tracks');
    }
};
