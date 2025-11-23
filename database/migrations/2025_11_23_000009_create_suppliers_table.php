<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('suppliers');

        Schema::create('suppliers', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('supplier_code', 255);
            $table->string('name', 191);
            $table->string('mobile', 191)->nullable();
            $table->string('address', 191)->nullable();
            $table->text('details')->nullable();
            $table->string('tax', 50)->nullable();
            $table->char('email', 100)->nullable();
            $table->text('previous_balance')->nullable();
            $table->tinyInteger('status')->nullable();

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};