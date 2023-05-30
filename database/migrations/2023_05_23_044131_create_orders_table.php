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
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->unique()->primary();
            $table->char('buyer_id',36);
            $table->foreign('buyer_id')->references('id')->on('buyers')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->char('merchant_id',36);
            $table->foreign('merchant_id')->references('id')->on('merchants')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->string('code',256)->nullable();
            $table->string('status',256);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
