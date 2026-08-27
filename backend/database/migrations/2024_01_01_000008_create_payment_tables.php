<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Payment Transactions
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('module_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_reference')->nullable(); // internal order ref
            $table->decimal('amount', 15, 2);
            $table->string('currency', 10)->default('IRR');
            $table->string('provider')->nullable(); // zarinpal, idpay, stripe, mock...
            $table->string('provider_transaction_id')->nullable();
            $table->string('status')->default('pending');
            // pending, redirected, paid, failed, cancelled, refunded, partially_refunded
            $table->string('idempotency_key')->nullable()->unique();
            $table->json('request_payload')->nullable();
            $table->json('callback_payload')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamp('redirected_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
            $table->index(['provider', 'provider_transaction_id']);
        });

        // Simple invoices foundation
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_transaction_id')->nullable()->constrained('payment_transactions')->nullOnDelete();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->string('currency', 10)->default('IRR');
            $table->string('status')->default('draft'); // draft, issued, paid, cancelled
            $table->json('items')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('payment_transactions');
    }
};
