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
        Schema::table('units', function (Blueprint $table) {
            $table->string('type')->default('video'); // 'video', 'text_breakdown', 'audio_practice', 'html_custom'
            $table->json('content_data')->nullable(); // Stores text, image paths, or HTML strings
            $table->string('audio_url')->nullable(); // For pronunciation practice
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn(['type', 'content_data', 'audio_url']);
        });
    }
};
