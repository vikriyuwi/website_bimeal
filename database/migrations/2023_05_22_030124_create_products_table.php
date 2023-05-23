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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->unique()->primary();
            $table->char('merchant_id',36);
            $table->foreign('merchant_id')->references('id')->on('merchants')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->char('product_type_id',36);
            $table->foreign('product_type_id')->references('id')->on('product_types')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->string('name',256);
            $table->integer('price');
            $table->integer('stock');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
