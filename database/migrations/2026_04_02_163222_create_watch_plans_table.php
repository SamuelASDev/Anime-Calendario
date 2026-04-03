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
        Schema::create('watch_plans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('anime_id')->constrained()->cascadeOnDelete();

            $table->integer('episodes_watched')->default(0);
            $table->integer('episodes_per_day')->default(1);

            $table->date('start_date');

            $table->string('watch_status')->default('assistindo');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watch_plans');
    }
};
