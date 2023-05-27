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
        Schema::create('buyers', function (Blueprint $table) {
            $table->uuid('id')->unique()->primary();
            $table->string('username',256);
            $table->string('password',256);
            $table->string('email',256);
            $table->string('phone',16);
            $table->dateTime('verified_at')->nullable();
            $table->string('remember_token',256)->nullable();
            $table->string('name',256);
            $table->string('group',256);
            $table->string('group_id',256);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buyers');
    }
};
