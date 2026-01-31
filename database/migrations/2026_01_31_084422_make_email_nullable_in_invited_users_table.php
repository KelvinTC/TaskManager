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
        // SQLite doesn't support modifying columns, so we need to recreate the table
        if (DB::getDriverName() === 'sqlite') {
            // Disable foreign key checks
            DB::statement('PRAGMA foreign_keys=off;');

            // Create new table with nullable email
            DB::statement('
                CREATE TABLE invited_users_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name VARCHAR(255),
                    email VARCHAR(255),
                    phone_number VARCHAR(255),
                    role VARCHAR(255) DEFAULT "employee",
                    invited_by INTEGER NOT NULL,
                    registered BOOLEAN DEFAULT 0,
                    created_at TIMESTAMP,
                    updated_at TIMESTAMP,
                    FOREIGN KEY (invited_by) REFERENCES users(id) ON DELETE CASCADE
                )
            ');

            // Copy data
            DB::statement('
                INSERT INTO invited_users_new (id, name, email, phone_number, role, invited_by, registered, created_at, updated_at)
                SELECT id, name, email, phone_number, role, invited_by, registered, created_at, updated_at
                FROM invited_users
            ');

            // Drop old table
            DB::statement('DROP TABLE invited_users');

            // Rename new table
            DB::statement('ALTER TABLE invited_users_new RENAME TO invited_users');

            // Re-enable foreign key checks
            DB::statement('PRAGMA foreign_keys=on;');
        } else {
            // For MySQL/PostgreSQL, just modify the column
            Schema::table('invited_users', function (Blueprint $table) {
                $table->string('email')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // For rollback, we'd need to make email NOT NULL again
        // This is destructive if there are NULL emails, so we skip it
    }
};
