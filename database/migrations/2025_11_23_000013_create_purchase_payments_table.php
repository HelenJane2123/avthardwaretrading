<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('purchase_payments');

        Schema::create('purchase_payments', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('purchase_id');

            $table->decimal('amount_paid', 12, 2);
            $table->decimal('outstanding_balance', 20, 6)->nullable();
            $table->date('payment_date')->nullable();
            $table->enum('payment_status', ['paid', 'partial'])->nullable();

            $table->timestamp('created_at')->useCurrent()->nullable();
            $table->timestamp('updated_at')->nullable();

            // Index
            $table->index('purchase_id', 'purchase_id');

            // Foreign key
            $table->foreign('purchase_id')
                  ->references('id')->on('purchases')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_payments');
    }
};