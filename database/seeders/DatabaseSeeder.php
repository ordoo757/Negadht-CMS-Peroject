<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // اگر هیچ admin وجود نداشت، خودکار بساز
        if (!User::where('role', 'admin')->exists()) {
            User::create([
                'name' => 'مدیر سیستم',
                'email' => 'admin@neurocms.ir',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'is_active' => true,
            ]);
            $this->command->info('✅ کاربر admin پیش‌فرض ساخته شد.');
            $this->command->warn('   ایمیل: admin@neurocms.ir');
            $this->command->warn('   رمز: admin123');
        }
    }
}
