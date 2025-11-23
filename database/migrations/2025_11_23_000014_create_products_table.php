<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('products');

        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('product_code', 255);
            $table->string('supplier_product_code', 255)->nullable();
            $table->text('product_name');
            $table->text('description')->nullable();
            $table->integer('serial_number')->default(0);
            $table->string('model', 191)->nullable();
            $table->integer('category_id');
            $table->string('sales_price', 191);
            $table->integer('unit_id');
            $table->integer('quantity')->nullable();
            $table->integer('remaining_stock')->nullable();
            $table->string('tax_id', 191)->nullable();
            $table->string('image', 191)->nullable();
            $table->integer('threshold')->nullable();
            $table->enum('status', ['In Stock', 'Low Stock', 'Out of Stock'])->default('In Stock');
            $table->string('volume_less', 50)->nullable();
            $table->string('regular_less', 50)->nullable();

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};