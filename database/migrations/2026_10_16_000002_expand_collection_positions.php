<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_anime_meta', function (Blueprint $table) {
            $table->bigInteger('favorite_position')->nullable()->change();
            $table->bigInteger('completed_position')->nullable()->change();
            $table->index(['user_id', 'is_favorite', 'favorite_position'], 'meta_favorite_order');
        });
    }

    public function down(): void
    {
        // Keep widened columns: narrowing could destroy persisted ordering values.
        Schema::table('user_anime_meta', fn (Blueprint $table) => $table->dropIndex('meta_favorite_order'));
    }
};
