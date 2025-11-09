<?php

namespace Database\Seeders;

use App\Models\Permission;
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
        $adminRole = Role::firstOrCreate(['name' => 'Administrator']);
        $guestRole = Role::firstOrCreate(['name' => 'Guest']);

        $permissions = collect([
            'users:manage',
            'products:import',
            'products:view'
        ])->map(fn ($name) => Permission::firstOrCreate(['name' => $name]));

        $adminRole->givePermissionTo($permissions);
        $guestRole->givePermissionTo(Permission::firstOrCreate(['name' => 'products:view']));

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
