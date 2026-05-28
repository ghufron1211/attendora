<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Ensure admin user exists with correct credentials.
     * Uses DB::table() to bypass Eloquent casts and explicitly hash.
     *
     * Email: admin@gmail.com
     * Password: admin123
     */
    public function run(): void
    {
        $now = now();
        $hashedPassword = Hash::make('admin123');

        $admin = DB::table('users')->where('email', 'admin@gmail.com')->first();

        if ($admin) {
            DB::table('users')
                ->where('email', 'admin@gmail.com')
                ->update([
                    'password' => $hashedPassword,
                    'role' => 'admin',
                    'deleted_at' => null,
                    'updated_at' => $now,
                ]);
            $this->command->info('✅ Admin password reset: admin@gmail.com / admin123');
        } else {
            // Also check for old admin@admin.com from migration
            $oldAdmin = DB::table('users')->where('email', 'admin@admin.com')->first();
            if ($oldAdmin) {
                DB::table('users')
                    ->where('email', 'admin@admin.com')
                    ->update([
                        'email' => 'admin@gmail.com',
                        'password' => $hashedPassword,
                        'role' => 'admin',
                        'deleted_at' => null,
                        'updated_at' => $now,
                    ]);
                $this->command->info('✅ Admin migrated: admin@admin.com → admin@gmail.com / admin123');
            } else {
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
                $this->command->info('✅ Admin created: admin@gmail.com / admin123');
            }
        }
    }
}
