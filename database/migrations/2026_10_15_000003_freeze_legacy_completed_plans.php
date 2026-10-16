<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // There is no historical completion timestamp; updated_at is the best retained boundary.
        DB::table('watch_plans')->where('watch_status', 'concluido')->whereNull('completed_at')
            ->update(['completed_at' => DB::raw('COALESCE(updated_at, created_at, CURRENT_TIMESTAMP)')]);
    }

    public function down(): void
    {
        // Keep historical boundaries rather than erasing completion information.
    }
};
