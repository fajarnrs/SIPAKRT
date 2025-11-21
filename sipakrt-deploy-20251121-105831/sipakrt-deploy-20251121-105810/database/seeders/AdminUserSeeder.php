<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@warga.test'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('admin123'),
                'is_admin' => true,
                'role' => 'admin',
                'rt_id' => null,
                'is_active' => true,
                'household_id' => null,
            ],
        );
    }
}
