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
        Schema::create('merchants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('username',256)->unique();
            $table->string('password',256)->unique();
            $table->string('email',256);
            $table->string('phone',16)->unique();
            $table->dateTime('verified_at')->nullable();
            $table->string('remember_token',256)->nullable();
            $table->string('name',255);
            $table->string('location_number',256);
            $table->string('time_open',32);
            $table->string('time_close',32);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchants');
    }
};
