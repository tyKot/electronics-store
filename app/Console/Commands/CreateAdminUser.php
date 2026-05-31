<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'app:create-admin {--email=admin@example.com} {--password=password}';
    protected $description = 'Создать пользователя с ролью администратора';

    public function handle()
    {
        // Создаём роль, если её нет
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        $user = User::firstOrCreate(
            ['email' => $this->option('email')],
            [
                'name' => 'Administrator',
                'password' => Hash::make($this->option('password')),
            ]
        );

        $user->assignRole($adminRole);

        $this->info("Админ создан: {$user->email}");
    }
}
