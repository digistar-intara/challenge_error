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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id_user')->primary();
            $table->string('full_name');
            $table->string('username')->unique(); 
            $table->timestamp('account_verified_at')->nullable();
            $table->string('otp')->nullable();
            $table->string('password');
            $table->string('phone_number')->unique();
            $table->string('address');
            $table->string('nik')->unique();
            $table->longText('user_image')->nullable();
            $table->string('role')->default('user');
            $table->longText('nik_image');
            $table->longText('ktm_image');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
