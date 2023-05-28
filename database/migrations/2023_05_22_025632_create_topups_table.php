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
        Schema::create('topups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->char('buyer_id',36);
            $table->foreign('buyer_id')->references('id')->on('buyers')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->integer('debt');
            $table->string('status');
            $table->char('admin_id',36);
            $table->foreign('admin_id')->references('id')->on('admins')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topups');
    }
};
