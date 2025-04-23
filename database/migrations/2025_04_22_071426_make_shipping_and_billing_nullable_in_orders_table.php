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
        Schema::table('orders', function (Blueprint $table) {
            // Make shipping fields nullable
            $table->string('shipping_name')->nullable()->change();
            $table->string('shipping_email')->nullable()->change();
            $table->string('shipping_phone')->nullable()->change();
            $table->string('shipping_address')->nullable()->change();
            $table->string('shipping_city')->nullable()->change();
            $table->string('shipping_zip')->nullable()->change();
            $table->string('shipping_country')->nullable()->change();

            // Make billing fields nullable
            $table->string('billing_name')->nullable()->change();
            $table->string('billing_email')->nullable()->change();
            $table->string('billing_phone')->nullable()->change();
            $table->string('billing_address')->nullable()->change();
            $table->string('billing_city')->nullable()->change();
            $table->string('billing_zip')->nullable()->change();
            $table->string('billing_country')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Revert shipping fields to required
            $table->string('shipping_name')->nullable(false)->change();
            $table->string('shipping_email')->nullable(false)->change();
            $table->string('shipping_phone')->nullable(false)->change();
            $table->string('shipping_address')->nullable(false)->change();
            $table->string('shipping_city')->nullable(false)->change();
            $table->string('shipping_zip')->nullable(false)->change();
            $table->string('shipping_country')->nullable(false)->change();

            // Revert billing fields to required
            $table->string('billing_name')->nullable(false)->change();
            $table->string('billing_email')->nullable(false)->change();
            $table->string('billing_phone')->nullable(false)->change();
            $table->string('billing_address')->nullable(false)->change();
            $table->string('billing_city')->nullable(false)->change();
            $table->string('billing_zip')->nullable(false)->change();
            $table->string('billing_country')->nullable(false)->change();
        });
    }
};
