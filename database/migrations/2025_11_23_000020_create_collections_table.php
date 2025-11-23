<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->bigIncrements('id'); // BIGINT UNSIGNED AUTO_INCREMENT primary key
            $table->string('collection_number', 50)->unique();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('customer_id');

            $table->timestamp('check_date')->nullable();
            $table->string('check_number', 100)->nullable();
            $table->decimal('check_amount', 12, 2)->nullable();
            $table->string('bank_name', 50)->nullable();
            $table->string('gcash_number', 100)->nullable();
            $table->string('gcash_name', 50)->nullable();
            $table->date('payment_date')->nullable();
            $table->decimal('last_paid_amount', 12, 2)->nullable();
            $table->decimal('amount_paid', 12, 2);
            $table->text('remarks')->nullable();
            $table->integer('is_approved')->nullable();

            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            // Indexes
            $table->index('invoice_id');
            $table->index('customer_id');

            // Foreign keys
            $table->foreign('invoice_id')
                ->references('id')->on('invoices')
                ->onDelete('cascade');

            $table->foreign('customer_id')
                ->references('id')->on('customers')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};
