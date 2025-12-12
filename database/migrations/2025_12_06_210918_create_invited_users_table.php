<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invited_users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->enum('role', ['admin', 'employee'])->default('employee');
            $table->foreignId('invited_by')->constrained('users')->onDelete('cascade');
            $table->boolean('registered')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invited_users');
    }
};
