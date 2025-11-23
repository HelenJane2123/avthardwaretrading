<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_sales', function (Blueprint $table) {
            $table->bigIncrements('id'); // BIGINT UNSIGNED AUTO_INCREMENT primary key
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('qty');
            $table->decimal('price', 10, 2);
            $table->decimal('dis', 10, 2)->default(0.00);
            $table->decimal('amount', 10, 2);

            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            // Indexes
            $table->index('invoice_id', 'fk_sales_invoice');
            $table->index('product_id', 'fk_sales_product');

            // Foreign keys
            $table->foreign('invoice_id')
                ->references('id')->on('invoices')
                ->onDelete('cascade');

            $table->foreign('product_id')
                ->references('id')->on('products')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_sales');
    }
};
