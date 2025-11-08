<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $guestRole = Role::firstOrCreate(['name' => 'guest']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@detik.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('Admin123!')
            ]
        );

        $guest = User::firstOrCreate(
            ['email' => 'guest@detik.com'],
            [
                'name' => 'Guest',
                'password' => Hash::make('Guest123!')
            ]
        );

        $admin->assignRole($adminRole);
        $guest->assignRole($guestRole);
    }
}
