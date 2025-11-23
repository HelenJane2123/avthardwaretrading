<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adjustment_collections', function (Blueprint $table) {
            $table->bigIncrements('id'); // BIGINT UNSIGNED AUTO_INCREMENT primary key
            $table->string('adjustment_no', 100)->nullable();
            $table->string('invoice_no', 100)->nullable();
            $table->enum('entry_type', ['Debit', 'Credit']);
            $table->date('collection_date');
            $table->string('account_name', 255);
            $table->decimal('amount', 20, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->integer('is_approved')->nullable();

            // Timestamps with defaults
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            // Index
            $table->index('invoice_no', 'invoice_no_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adjustment_collections');
    }
};
