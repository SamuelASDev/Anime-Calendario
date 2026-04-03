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
        Schema::create('watch_plan_days', function (Blueprint $table) {
            $table->id();

            $table->foreignId('watch_plan_id')->constrained()->cascadeOnDelete();

            $table->integer('day_of_week'); // 0 = domingo, 6 = sábado

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watch_plan_days');
    }
};
