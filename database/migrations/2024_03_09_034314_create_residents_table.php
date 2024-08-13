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
        Schema::create('residents', function (Blueprint $table) {
            $table->uuid('id_resident')->primary();
            $table->uuid('id_user');
            $table->foreign('id_user')->references('id_user')->on('users');
            $table->uuid('id_occupy');
            $table->foreign('id_occupy')->references('id_occupy')->on('occupies');
            $table->string('resident_name');
            $table->string('resident_phone_number')->unique();
            $table->string('resident_address');
            $table->string('resident_nik')->unique();
            $table->longText('resident_user_image')->nullable();
            $table->longText('resident_nik_image');
            $table->longText('resident_ktm_image');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('residents');
    }
};
