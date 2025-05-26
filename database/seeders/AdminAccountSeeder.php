<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;
use App\Models\Role;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Hash;

class AdminAccountSeeder extends Seeder
{
    public function run()
    {
        $adminRole = Role::where('name', 'admin')->first();

        if (!$adminRole) {
            $this->command->error('Role "admin" not found. Please run `php artisan db:seed --class=RoleSeeder` first.');
            return;
        }

        $account = Account::updateOrCreate([
            'email' => 'admin@example.com',
        ], [
            'password' => Hash::make('admin123'),
            'role_id' => $adminRole->id,
        ]);

        UserProfile::updateOrCreate([
            'account_id' => $account->id,
        ], [
            'name' => 'Admin',
        ]);
    }
}
