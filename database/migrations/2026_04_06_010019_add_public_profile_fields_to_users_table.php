<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->nullable()->after('name');
            $table->text('bio')->nullable()->after('username');

            $table->boolean('is_public')->default(true)->after('bio');
            $table->boolean('show_watching_public')->default(true)->after('is_public');
            $table->boolean('show_completed_public')->default(true)->after('show_watching_public');
            $table->boolean('show_favorites_public')->default(true)->after('show_completed_public');
            $table->boolean('show_top10_public')->default(true)->after('show_favorites_public');
            $table->boolean('show_reviews_public')->default(true)->after('show_top10_public');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'bio',
                'is_public',
                'show_watching_public',
                'show_completed_public',
                'show_favorites_public',
                'show_top10_public',
                'show_reviews_public',
            ]);
        });
    }
};
