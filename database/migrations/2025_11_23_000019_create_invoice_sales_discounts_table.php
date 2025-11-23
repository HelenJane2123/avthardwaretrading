<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_sales_discounts', function (Blueprint $table) {
            $table->bigIncrements('id'); // BIGINT UNSIGNED AUTO_INCREMENT primary key
            $table->unsignedBigInteger('invoice_sale_id');
            $table->string('discount_name', 100)->nullable();
            $table->enum('discount_type', ['percent', 'fixed'])->default('percent');
            $table->integer('discount_value')->default(0);

            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            // Index
            $table->index('invoice_sale_id', 'fk_invoice_sales_discounts_sale');

            // Foreign key
            $table->foreign('invoice_sale_id')
                ->references('id')->on('invoice_sales')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_sales_discounts');
    }
};
