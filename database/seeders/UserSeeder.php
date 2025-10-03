<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample roles if they don't exist
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee']);
        $viewerRole = Role::firstOrCreate(['name' => 'viewer']);

        // Create Manager User
        $manager = User::create([
            'name' => 'John Manager',
            'email' => 'manager@inphaautomac.lk',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        $manager->assignRole('manager');

        // Create Employee User
        $employee = User::create([
            'name' => 'Jane Employee',
            'email' => 'employee@inphaautomac.lk',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        $employee->assignRole('employee');

        // Create Viewer User
        $viewer = User::create([
            'name' => 'Bob Viewer',
            'email' => 'viewer@inphaautomac.lk',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        $viewer->assignRole('viewer');

        // Create a user with multiple roles
        $multiRole = User::create([
            'name' => 'Alice Multi-Role',
            'email' => 'multirole@inphaautomac.lk',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        $multiRole->assignRole(['manager', 'employee']);

        $this->command->info('Sample users created successfully!');
        $this->command->table(
            ['Name', 'Email', 'Roles'],
            [
                [$manager->name, $manager->email, 'manager'],
                [$employee->name, $employee->email, 'employee'],
                [$viewer->name, $viewer->email, 'viewer'],
                [$multiRole->name, $multiRole->email, 'manager, employee'],
            ]
        );
    }
}