<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['watch_plans' => ['anime_id', 'user_id'], 'watch_plan_days' => ['watch_plan_id', 'day_of_week'],
            'watch_logs' => ['watch_plan_id', 'watched_date'], 'user_anime_meta' => ['user_id', 'anime_id']] as $table => $columns) {
            if (DB::table($table)->select($columns)->groupBy($columns)->havingRaw('COUNT(*) > 1')->exists()) {
                throw new RuntimeException("Duplicate rows in {$table}; resolve them explicitly before migrating.");
            }
        }

        // MariaDB rejects a STORED column based on an ON DELETE SET NULL key.
        // Change the legacy foreign key before adding its dependent generated column.
        Schema::table('watch_plans', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
        Schema::table('watch_plans', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_key')->storedAs('COALESCE(user_id, 0)');
            $table->unique(['anime_id', 'owner_key']);
            $table->index(['user_id', 'watch_status', 'updated_at']);
            $table->timestamp('completed_at')->nullable();
        });
        Schema::table('watch_plan_days', fn (Blueprint $table) => $table->unique(['watch_plan_id', 'day_of_week']));
        Schema::table('watch_logs', fn (Blueprint $table) => $table->unique(['watch_plan_id', 'watched_date']));
        Schema::table('user_anime_meta', function (Blueprint $table) {
            $table->unique(['user_id', 'anime_id']);
            $table->index(['user_id', 'top_position']);
        });
        Schema::create('watch_plan_pauses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('watch_plan_id')->constrained()->cascadeOnDelete();
            $table->date('started_on');
            $table->date('resumed_on')->nullable();
            $table->timestamps();
        });
        // The exact past pause date was never recorded; freeze legacy pauses from today.
        $now = now();
        DB::table('watch_plans')->where('watch_status', 'pausado')->orderBy('id')->chunkById(200, function ($plans) use ($now) {
            DB::table('watch_plan_pauses')->insert($plans->map(fn ($plan) => [
                'watch_plan_id' => $plan->id, 'started_on' => $now->toDateString(),
                'created_at' => $now, 'updated_at' => $now,
            ])->all());
        });
    }

    public function down(): void
    {
        // InnoDB may remove implicit FK indexes once composite indexes cover them.
        // Restore standalone indexes before dropping those composite indexes.
        foreach (['watch_plans' => ['anime_id', 'user_id'], 'watch_plan_days' => ['watch_plan_id'],
            'watch_logs' => ['watch_plan_id'], 'user_anime_meta' => ['user_id', 'anime_id']] as $name => $columns) {
            foreach ($columns as $column) {
                $standalone = collect(Schema::getIndexes($name))->contains(fn ($index) => $index['columns'] === [$column]);
                if (! $standalone) {
                    Schema::table($name, fn (Blueprint $table) => $table->index($column, $name.'_'.$column.'_rollback_index'));
                }
            }
        }
        Schema::dropIfExists('watch_plan_pauses');
        Schema::table('user_anime_meta', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'anime_id']);
            $table->dropIndex(['user_id', 'top_position']);
        });
        Schema::table('watch_logs', fn (Blueprint $table) => $table->dropUnique(['watch_plan_id', 'watched_date']));
        Schema::table('watch_plan_days', fn (Blueprint $table) => $table->dropUnique(['watch_plan_id', 'day_of_week']));
        Schema::table('watch_plans', function (Blueprint $table) {
            $table->dropUnique(['anime_id', 'owner_key']);
            $table->dropIndex(['user_id', 'watch_status', 'updated_at']);
            $table->dropColumn(['owner_key', 'completed_at']);
        });
        Schema::table('watch_plans', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }
};
