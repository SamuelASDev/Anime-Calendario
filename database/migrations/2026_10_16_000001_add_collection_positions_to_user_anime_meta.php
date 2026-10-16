<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_anime_meta', function (Blueprint $table) {
            $table->unsignedInteger('favorite_position')->nullable();
            $table->unsignedInteger('completed_position')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('user_anime_meta', fn (Blueprint $table) => $table->dropColumn(['favorite_position', 'completed_position']));
    }
};
