<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('f_name', 191);
            $table->string('l_name', 191);
            $table->string('contact', 50)->nullable();
            $table->string('email', 191)->unique();
            $table->string('image', 191)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 191);
            $table->string('user_role', 50)->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->enum('user_status', ['active', 'inactive'])->nullable();
            $table->integer('password_reset_flag')->nullable();

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};