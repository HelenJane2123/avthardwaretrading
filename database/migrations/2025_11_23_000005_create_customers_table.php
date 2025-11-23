<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('customers');

        Schema::create('customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('customer_code', 50)->nullable();
            $table->string('name', 191);
            $table->string('mobile', 191)->nullable();
            $table->string('address', 191);
            $table->string('email', 150)->nullable();
            $table->string('tax', 50)->nullable();
            $table->text('details')->nullable();
            $table->tinyInteger('status')->nullable();
            $table->text('previous_balance')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};