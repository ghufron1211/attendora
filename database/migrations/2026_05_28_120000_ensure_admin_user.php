<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Ensure a working admin account exists.
     * This migration handles all edge cases:
     * - Updates existing admin@gmail.com password
     * - Updates existing admin@admin.com email + password
     * - Creates admin if neither exists
     */
    public function up(): void
    {
        $now = now();
        $hashedPassword = Hash::make('admin123');

        // Check if admin@gmail.com exists
        $adminGmail = DB::table('users')
            ->where('email', 'admin@gmail.com')
            ->first();

        if ($adminGmail) {
            // Update password and ensure not soft-deleted
            DB::table('users')
                ->where('email', 'admin@gmail.com')
                ->update([
                    'password' => $hashedPassword,
                    'role' => 'admin',
                    'deleted_at' => null,
                    'updated_at' => $now,
                ]);
            return;
        }

        // Check if the migration-created admin@admin.com exists
        $adminOld = DB::table('users')
            ->where('email', 'admin@admin.com')
            ->first();

        if ($adminOld) {
            // Update to new email and password
            DB::table('users')
                ->where('email', 'admin@admin.com')
                ->update([
                    'email' => 'admin@gmail.com',
                    'password' => $hashedPassword,
                    'role' => 'admin',
                    'deleted_at' => null,
                    'updated_at' => $now,
                ]);
            return;
        }

        // Neither exists — create fresh
        DB::table('users')->insert([
            'username' => 'admin',
            'name' => 'Admin Mentor',
            'email' => 'admin@gmail.com',
            'no_telp' => '081234567890',
            'asal_instansi' => 'Attendora Platform',
            'role' => 'admin',
            'face_data' => 'admin_face_data',
            'password' => $hashedPassword,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed
    }
};
