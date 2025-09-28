<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // إنشاء مستخدم تجريبي
        // User::factory()->create([
        //     'full_name' => 'Test User',
        //     'email' => 'test@gmail.com',
        //     'phone' => '1234567890',
        //     'password' => bcrypt('password123'),
        //     'role' => 'admin',
        //     'is_verified' => true,
        // ]);

        // تشغيل Seeder الخاص بالأصول المالية
        $this->call(AssetsTableSeeder::class);
    }
}
