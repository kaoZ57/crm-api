<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
//use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $admin = User::firstWhere('email', 'admin@admin.com');
        if (!$admin) {
            $adminUser = User::create([
                'name'  => 'Admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('admin123')
            ]);
            $roleAdmin = Role::findOrCreate('admin', 'api');
            $roleUser  = Role::findOrCreate('user', 'api');
            $adminUser->assignRole([$roleAdmin, $roleUser]);

            $kaoUser = User::create([
                'name'  => 'kao',
                'email' => 'kao@admin.com',
                'password' => Hash::make('123456')
            ]);
            $kaoUser->assignRole([$roleAdmin, $roleUser]);

            $tanUser = User::create([
                'name'  => 'Tan1',
                'email' => 'Tan1@gmail.com',
                'password' => Hash::make('123456')
            ]);
            $tanUser->assignRole([$roleUser]);

            $tanUser = User::create([
                'name'  => 'nan',
                'email' => 'nan@nan.com',
                'password' => Hash::make('123456')
            ]);
            $tanUser->assignRole([$roleUser]);
        }
    }
}
