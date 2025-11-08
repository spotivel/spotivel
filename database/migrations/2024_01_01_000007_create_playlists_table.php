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
        Schema::create('playlists', function (Blueprint $table) {
            $table->id();
            $table->string('spotify_id')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('public')->default(true);
            $table->boolean('collaborative')->default(false);
            $table->integer('total_tracks')->default(0);
            $table->string('uri')->nullable();
            $table->string('href')->nullable();
            $table->string('external_url')->nullable();
            $table->string('owner_id')->nullable();
            $table->string('owner_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('playlists');
    }
};
