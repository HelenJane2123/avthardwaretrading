<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('product_adjustments');

        Schema::create('product_adjustments', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('product_id');

            $table->integer('adjustment')->default(0);
            $table->enum('adjustment_status', ['Return', 'Others'])->default('Others');
            $table->text('remarks')->nullable();
            $table->integer('new_initial_qty')->default(0);

            $table->timestamp('created_at')->useCurrent()->nullable();
            $table->timestamp('updated_at')->useCurrent()->nullable()->useCurrentOnUpdate();

            // Index
            $table->index('product_id', 'fk_adjustment_product');

            // Foreign key
            $table->foreign('product_id')
                  ->references('id')->on('products')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_adjustments');
    }
};