<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        Role::insert([
            [
                'name' => 'admin',
                'description' => 'Administrator role',
            ],
            [
                'name' => 'user',
                'description' => 'Regular user role',
            ]
        ]);
    }
}
