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
        Schema::create('occupies', function (Blueprint $table) {
            $table->uuid('id_occupy')->primary();
            $table->uuid('id_user');
            $table->foreign('id_user')->references('id_user')->on('users');
            $table->uuid('id_room');
            $table->foreign('id_room')->references('id_room')->on('rooms');
            $table->date('check_in');
            $table->string('subscription_model');
            $table->string('status_occupy')->default('pending');
            $table->string('is_double_bed')->default('no');
            $table->date('check_out')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('occupies');
    }
};
