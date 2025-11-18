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
            $table->id();
            $table->string('order_number')->unique(); // INV-20250101-001
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // nullable untuk walk-in customer

            // Customer Info (untuk walk-in tanpa akun)
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();

            // Order Details
            $table->enum('order_type', ['dine_in', 'takeaway'])->default('dine_in');
            $table->string('table_number')->nullable(); // untuk dine-in
            $table->text('notes')->nullable();

            // Pricing
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2)->default(0); // PB1 10%
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2);

            // Order Status
            $table->enum('status', ['pending', 'preparing', 'ready', 'completed', 'cancelled'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancelled_reason')->nullable();

            $table->foreignId('created_by')->nullable()->references('id')->on('admins')->nullOnDelete(); // staff yang input
            $table->timestamps();
            $table->softDeletes();
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
