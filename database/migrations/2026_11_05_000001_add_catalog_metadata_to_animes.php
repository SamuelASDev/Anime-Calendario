<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Additive only: the code currently in production keeps working before and after this runs. */
    public function up(): void
    {
        Schema::table('animes', function (Blueprint $table) {
            $table->string('media_type', 30)->nullable();
            $table->string('source_material', 60)->nullable();
            $table->date('aired_from')->nullable()->index();
            $table->date('aired_to')->nullable();
            $table->string('aired_precision', 5)->nullable();
            $table->string('season', 10)->nullable();
            $table->unsignedSmallInteger('season_year')->nullable();
            $table->string('duration', 60)->nullable();
            $table->string('age_rating', 60)->nullable();
            $table->string('studios', 500)->nullable();
            $table->timestamp('metadata_synced_at')->nullable()->index();
            $table->string('metadata_sync_error', 30)->nullable()->index();
        });

        Schema::create('anime_titles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anime_id')->constrained('animes')->cascadeOnDelete();
            $table->string('type', 30);
            $table->string('title');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anime_titles');
        Schema::table('animes', function (Blueprint $table) {
            $table->dropIndex(['aired_from']);
            $table->dropIndex(['metadata_synced_at']);
            $table->dropIndex(['metadata_sync_error']);
            $table->dropColumn(['media_type', 'source_material', 'aired_from', 'aired_to', 'aired_precision', 'season', 'season_year', 'duration', 'age_rating', 'studios', 'metadata_synced_at', 'metadata_sync_error']);
        });
    }
};
