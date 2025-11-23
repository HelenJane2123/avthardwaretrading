<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->bigIncrements('id'); // BIGINT UNSIGNED AUTO_INCREMENT primary key
            $table->string('invoice_number', 100)->nullable();
            $table->unsignedBigInteger('customer_id');
            $table->timestamp('invoice_date');
            $table->timestamp('due_date');
            $table->unsignedBigInteger('payment_mode_id');
            $table->string('discount_type', 50)->default('0');
            $table->decimal('discount_value', 10, 2)->default(0.00);
            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('shipping_fee', 10, 2)->default(0.00);
            $table->decimal('other_charges', 10, 2)->default(0.00);
            $table->decimal('grand_total', 10, 2)->default(0.00);
            $table->decimal('outstanding_balance', 10, 2)->nullable();
            $table->enum('invoice_status', ['pending', 'canceled', 'approved'])->default('pending');
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'overdue'])->nullable();
            $table->string('salesman', 50)->nullable();
            $table->text('remarks')->nullable();
            $table->integer('discount_approved')->nullable();

            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            // Indexes
            $table->index('customer_id', 'fk_invoices_customer');
            $table->index('payment_mode_id', 'fk_invoices_payment');

            // Foreign keys
            $table->foreign('customer_id')
                ->references('id')->on('customers')
                ->onDelete('cascade');

            $table->foreign('payment_mode_id')
                ->references('id')->on('mode_of_payment')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
