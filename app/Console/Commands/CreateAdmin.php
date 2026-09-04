<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create 
                            {--email=admin@neurocms.ir : ایمیل admin}
                            {--password= : رمز عبور (اگر وارد نشود، خودکار تولید می‌شود)}
                            {--name=مدیر سیستم : نام admin}';

    protected $description = 'ساخت یا بازنشانی کاربر admin';

    public function handle(): int
    {
        $email = $this->option('email');
        $name = $this->option('name');
        $password = $this->option('password');

        // اگر رمز وارد نشد، خودکار تولید کن
        if (!$password) {
            $password = bin2hex(random_bytes(4)); // 8 کاراکتر تصادفی
            $this->warn("⚠️  رمز عبور خودکار تولید شد: $password");
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $this->info("✅ کاربر admin با موفقیت ساخته/بروزرسانی شد:");
        $this->table(
            ['فیلد', 'مقدار'],
            [
                ['نام', $name],
                ['ایمیل', $email],
                ['رمز عبور', $password],
                ['نقش', 'admin'],
            ]
        );

        return 0;
    }
}
