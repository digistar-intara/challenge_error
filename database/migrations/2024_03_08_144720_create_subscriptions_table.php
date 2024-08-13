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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id_subscription')->primary();
            $table->uuid('id_user');
            $table->foreign('id_user')->references('id_user')->on('users');
            $table->uuid('id_occupy');
            $table->foreign('id_occupy')->references('id_occupy')->on('occupies');
            $table->longText('payment_receipt')->nullable();
            $table->string('subscription_status');
            $table->date('subscription_date');
            $table->date('subscription_end_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
