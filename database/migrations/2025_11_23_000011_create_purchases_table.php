<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('supplier_id');
            $table->string('po_number', 100)->unique();

            $table->unsignedBigInteger('salesman_id');
            $table->unsignedInteger('payment_id')->nullable();

            $table->string('gcash_number', 50)->nullable();
            $table->string('gcash_name', 50)->nullable();
            $table->string('check_number', 50)->nullable();

            $table->date('date');

            $table->enum('discount_type', ['per_item', 'overall', 'all'])->default('per_item');
            $table->decimal('discount_value', 10, 2)->default(0.00);
            $table->decimal('overall_discount', 5, 2)->nullable();
            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('shipping', 10, 2)->default(0.00);
            $table->decimal('other_charges', 10, 2)->default(0.00);

            $table->text('remarks')->nullable();
            $table->decimal('grand_total', 10, 2)->default(0.00);
            $table->integer('is_approved')->nullable();

            $table->timestamp('created_at')->useCurrent()->nullable();
            $table->timestamp('updated_at')->useCurrent()->nullable()->useCurrentOnUpdate();

            // Indexes
            $table->index('supplier_id', 'purchases_supplier_id_foreign');
            $table->index('salesman_id', 'fk_purchases_salesman');

            // Foreign keys
            $table->foreign('supplier_id')
                  ->references('id')->on('suppliers')
                  ->onDelete('cascade');

            $table->foreign('salesman_id')
              ->references('id')->on('salesman')
              ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};