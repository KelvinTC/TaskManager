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
        Schema::table('users', function (Blueprint $table) {
            // Change role enum to include super_admin, admin, and employee
            $table->dropColumn('role');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'admin', 'employee'])->default('employee')->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['client', 'worker'])->default('worker')->after('phone');
        });
    }
};
