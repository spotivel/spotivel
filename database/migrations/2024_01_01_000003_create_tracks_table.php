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
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->string('spotify_id')->unique();
            $table->string('name');
            $table->integer('duration_ms');
            $table->boolean('explicit')->default(false);
            $table->integer('disc_number')->default(1);
            $table->integer('track_number')->nullable();
            $table->integer('popularity')->nullable();
            $table->string('preview_url')->nullable();
            $table->string('uri')->nullable();
            $table->string('href')->nullable();
            $table->string('external_url')->nullable();
            $table->boolean('is_local')->default(false);
            $table->json('available_markets')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracks');
    }
};
