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
        Schema::table('tracks', function (Blueprint $table) {
            $table->boolean('is_interesting')->default(false)->after('is_local');
        });

        Schema::table('artists', function (Blueprint $table) {
            $table->boolean('is_interesting')->default(false)->after('followers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tracks', function (Blueprint $table) {
            $table->dropColumn('is_interesting');
        });

        Schema::table('artists', function (Blueprint $table) {
            $table->dropColumn('is_interesting');
        });
    }
};
