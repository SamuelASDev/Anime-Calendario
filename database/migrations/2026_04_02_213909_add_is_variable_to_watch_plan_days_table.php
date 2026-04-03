<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('watch_plan_days', function (Blueprint $table) {
            $table->boolean('is_variable')->default(false)->after('episodes_planned');
        });
    }

    public function down(): void
    {
        Schema::table('watch_plan_days', function (Blueprint $table) {
            $table->dropColumn('is_variable');
        });
    }
};