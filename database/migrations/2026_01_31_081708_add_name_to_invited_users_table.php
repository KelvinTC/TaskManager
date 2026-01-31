<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invited_users', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
        });

        // Update existing records: use email prefix as name if name is null
        DB::table('invited_users')
            ->whereNull('name')
            ->update(['name' => DB::raw("SUBSTR(email, 1, INSTR(email, '@') - 1)")]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invited_users', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
