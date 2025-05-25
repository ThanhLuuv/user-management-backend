<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminAccountSeeder extends Seeder
{
    public function run()
    {
        $adminRole = Role::where('name', 'admin')->first();

        if (!$adminRole) {
            $this->command->error('Role "admin" not found. Run RoleSeeder first.');
            return;
        }

        Account::updateOrCreate([
            'email' => 'admin@example.com',
        ], [
            'password' => Hash::make('admin123'),
            'role_id' => $adminRole->id,
        ]);
    }
}
