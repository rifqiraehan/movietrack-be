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
        Schema::create('watchlists', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('movie_id');
            $table->foreign('movie_id')->references('id')->on('movies')->onDelete('cascade');

            $table->unsignedBigInteger('status_id');
            $table->foreign('status_id')->references('id')->on('statuses')->onDelete('cascade');

            $table->float('score', 0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watchlists');
    }
};
