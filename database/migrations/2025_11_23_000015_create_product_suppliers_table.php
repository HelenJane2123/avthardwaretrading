<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('product_suppliers');

        Schema::create('product_suppliers', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('product_id');
            $table->integer('supplier_id');
            $table->integer('price');

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_suppliers');
    }
};