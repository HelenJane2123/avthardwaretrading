<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('mode_of_payment');

        Schema::create('mode_of_payment', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 50);
            $table->enum('term', ['15','30','45','60','90','120'])->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active'); // tinyint(1) → boolean
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mode_of_payment');
    }
};