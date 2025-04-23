<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if orders table has customer_id column
        if (Schema::hasColumn('orders', 'customer_id')) {
            // Check if the foreign key exists
            $foreignKeys = DB::select("
                SELECT constraint_name
                FROM information_schema.table_constraints
                WHERE constraint_type = 'FOREIGN KEY'
                AND table_name = 'orders'
                AND constraint_name = 'orders_customer_id_foreign'
            ");

            if (! empty($foreignKeys)) {
                Schema::table('orders', function (Blueprint $table) {
                    $table->dropForeign(['customer_id']);
                });
            }

            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('customer_id');
            });
        }

        Schema::dropIfExists('customers');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the customers table
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('country')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Add the customer_id column back to orders
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });
    }
};
