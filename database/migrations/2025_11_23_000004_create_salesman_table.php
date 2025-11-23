<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('salesman');

        Schema::create('salesman', function (Blueprint $table) {
            $table->bigIncrements('id'); // int AUTO_INCREMENT
            $table->string('salesman_code', 50)->unique();
            $table->string('salesman_name', 100);
            $table->string('phone', 20)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('email', 50)->nullable();
            $table->boolean('status')->default(1)->comment('1 = Active, 0 = Inactive');
            $table->timestamp('created_at')->useCurrent()->nullable();
            $table->timestamp('updated_at')->useCurrent()->nullable()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salesman');
    }
};