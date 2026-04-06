<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_anime_meta', function (Blueprint $table) {
            $table->boolean('is_favorite')->default(false)->after('comment');
            $table->unsignedTinyInteger('top_position')->nullable()->after('is_favorite');
        });
    }

    public function down(): void
    {
        Schema::table('user_anime_meta', function (Blueprint $table) {
            $table->dropColumn([
                'is_favorite',
                'top_position',
            ]);
        });
    }
};