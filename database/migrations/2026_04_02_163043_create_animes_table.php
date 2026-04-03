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
        Schema::create('animes', function (Blueprint $table) {
            $table->id();

            $table->integer('mal_id')->unique(); // id da Jikan
            $table->string('title');

            $table->integer('episodes')->nullable();

            $table->text('synopsis')->nullable();
            $table->string('anime_status')->nullable();

            $table->string('image')->nullable();

            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('animes');
    }
};
