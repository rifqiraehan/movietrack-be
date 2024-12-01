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
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('poster_path')->nullable();
            $table->float('vote_average', 1)->nullable();
            $table->string('production_name')->nullable();
            $table->string('duration')->nullable();
            $table->string('status')->nullable();
            $table->string('release_date')->nullable();
            $table->text('overview')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};