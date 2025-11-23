<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('supplier_items');

        Schema::create('supplier_items', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('supplier_id');
            $table->string('item_code', 255)->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('item_description', 350)->nullable();
            $table->decimal('item_price', 10, 2)->nullable();
            $table->decimal('item_amount', 10, 2)->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->integer('item_qty')->nullable();
            $table->integer('discount')->nullable();
            $table->string('item_image', 255)->nullable();
            $table->text('volume_less')->nullable();
            $table->text('regular_less')->nullable();

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Indexes
            $table->index('supplier_id', 'supplier_items_supplier_id_foreign');
            $table->index('category_id', 'fk_supplier_items_category');
            $table->index('unit_id', 'fk_supplier_items_unit');

            // Foreign keys
            $table->foreign('supplier_id')
                  ->references('id')->on('suppliers')
                  ->onDelete('cascade');

            $table->foreign('category_id')
                  ->references('id')->on('categories')
                  ->onDelete('set null');

            $table->foreign('unit_id')
                  ->references('id')->on('units')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_items');
    }
};