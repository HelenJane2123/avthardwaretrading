<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('purchase_items');

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('purchase_id');
            $table->unsignedBigInteger('supplier_item_id');

            $table->string('product_code', 50);
            $table->integer('qty');
            $table->decimal('unit_price', 10, 2);
            $table->integer('discount')->nullable();
            $table->string('unit', 50)->nullable();
            $table->decimal('total', 12, 2);

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Indexes
            $table->index('purchase_id', 'purchase_items_purchase_id_foreign');
            $table->index('supplier_item_id', 'purchase_items_supplier_item_id_foreign');

            // Foreign keys
            $table->foreign('purchase_id')
                  ->references('id')->on('purchases')
                  ->onDelete('cascade');

            $table->foreign('supplier_item_id')
                  ->references('id')->on('supplier_items')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};