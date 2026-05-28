<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Ensure admin user exists with correct credentials.
     * Safe to run multiple times (idempotent).
     *
     * Email: admin@gmail.com
     * Password: admin123
     */
    public function run(): void
    {
        $admin = User::withTrashed()->where('email', 'admin@gmail.com')->first();

        if ($admin) {
            // Restore if soft-deleted
            if ($admin->trashed()) {
                $admin->restore();
            }

            // Update password (User model 'hashed' cast auto-hashes)
            $admin->update([
                'password' => 'admin123',
                'role' => 'admin',
            ]);

            $this->command->info('✅ Admin user updated: admin@gmail.com / admin123');
        } else {
            User::create([
                'username' => 'admin',
                'name' => 'Admin Mentor',
                'email' => 'admin@gmail.com',
                'no_telp' => '081234567890',
                'asal_instansi' => 'Attendora Platform',
                'role' => 'admin',
                'face_data' => 'admin_face_data',
                'password' => 'admin123',
            ]);

            $this->command->info('✅ Admin user created: admin@gmail.com / admin123');
        }
    }
}
