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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
             $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // Payment Details
            $table->string('payment_number')->unique(); // PAY-20250101-001
            $table->decimal('amount', 10, 2); // bisa partial payment
            $table->enum('payment_method', ['cash', 'midtrans', 'qris', 'transfer'])->default('cash');
            $table->enum('status', ['pending', 'paid', 'failed', 'expired', 'refunded'])->default('pending');

            // Midtrans Integration
            $table->string('midtrans_order_id')->nullable();
            $table->string('midtrans_transaction_id')->nullable();
            $table->string('midtrans_snap_token')->nullable();
            $table->string('midtrans_payment_type')->nullable(); // credit_card, gopay, etc
            $table->text('midtrans_response')->nullable(); // JSON full response

            // Timestamps
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();

            // Notes & Reference
            $table->text('notes')->nullable();
            $table->string('reference_number')->nullable(); // bank ref, receipt number, etc

            $table->foreignId('processed_by')->nullable()->references('id')->on('admins')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
