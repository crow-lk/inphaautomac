<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\File;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create core permissions and admin role.
        // This list mirrors the abilities referenced in app/Policies so the admin role
        // receives all permissions those policies check for.
        $permissions = [
            // general/filament
            'view admin',
            'manage users',
            'manage roles',
            'manage permissions',

            // items
            'view_any_items','view_items','create_items','update_items','delete_items','delete_any_items','force_delete_items','force_delete_any_items','restore_items','restore_any_items','replicate_items','reorder_items',

            // modules
            'view_any_module','view_module','create_module','update_module','delete_module','delete_any_module','force_delete_module','force_delete_any_module','restore_module','restore_any_module','replicate_module','reorder_module',

            // invoices
            'view_any_invoice','view_invoice','create_invoice','update_invoice','delete_invoice','delete_any_invoice','force_delete_invoice','force_delete_any_invoice','restore_invoice','restore_any_invoice','replicate_invoice','reorder_invoice',

            // customers
            'view_any_customer','view_customer','create_customer','update_customer','delete_customer','delete_any_customer','force_delete_customer','force_delete_any_customer','restore_customer','restore_any_customer','replicate_customer','reorder_customer',

            // services (note plural in policies)
            'view_any_services','view_services','create_services','update_services','delete_services','delete_any_services','force_delete_services','force_delete_any_services','restore_services','restore_any_services','replicate_services','reorder_services',

            // vehicle
            'view_any_vehicle','view_vehicle','create_vehicle','update_vehicle','delete_vehicle','delete_any_vehicle','force_delete_vehicle','force_delete_any_vehicle','restore_vehicle','restore_any_vehicle','replicate_vehicle','reorder_vehicle',

            // procurement
            'view_any_procurement','view_procurement','create_procurement','update_procurement','delete_procurement','delete_any_procurement','force_delete_procurement','force_delete_any_procurement','restore_procurement','restore_any_procurement','replicate_procurement','reorder_procurement',

            // payment
            'view_any_payment','view_payment','create_payment','update_payment','delete_payment','delete_any_payment','force_delete_payment','force_delete_any_payment','restore_payment','restore_any_payment','replicate_payment','reorder_payment',

            // supplies
            'view_any_supplies','view_supplies','create_supplies','update_supplies','delete_supplies','delete_any_supplies','force_delete_supplies','force_delete_any_supplies','restore_supplies','restore_any_supplies','replicate_supplies','reorder_supplies',

            // issue battery packs (these use :: in the policy names)
            'view_any_issue::battery::packs','view_issue::battery::packs','create_issue::battery::packs','update_issue::battery::packs','delete_issue::battery::packs','delete_any_issue::battery::packs','force_delete_issue::battery::packs','force_delete_any_issue::battery::packs','restore_issue::battery::packs','restore_any_issue::battery::packs','replicate_issue::battery::packs','reorder_issue::battery::packs',

            // roles
            'view_any_role','view_role','create_role','update_role','delete_role','delete_any_role','force_delete_role','force_delete_any_role','restore_role','restore_any_role','replicate_role','reorder_role',

            // users (add user permissions in case some checks use them)
            'view_any_user','view_user','create_user','update_user','delete_user','delete_any_user','force_delete_user','force_delete_any_user','restore_user','restore_any_user','replicate_user','reorder_user',
        ];

        // Also auto-discover permission strings used inside policy files
        $policyPath = app_path('Policies');
        if (File::exists($policyPath)) {
            $policyFiles = File::files($policyPath);
            foreach ($policyFiles as $file) {
                $contents = File::get($file->getPathname());
                // match $user->can('permission_name') occurrences
                if (preg_match_all("/->can\(\s*'([^']+)'\s*\)/", $contents, $matches)) {
                    foreach ($matches[1] as $m) {
                        // skip placeholder-like entries with { or }
                        if (str_contains($m, '{') || str_contains($m, '}')) {
                            continue;
                        }
                        $permissions[] = $m;
                    }
                }
            }
            // unique and filter
            $permissions = array_values(array_unique(array_filter($permissions)));
        }

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        // assign all permissions to admin role
        $adminRole->syncPermissions(Permission::all());

        // Create admin user (idempotent)
        $admin = User::firstOrCreate(
            ['email' => 'admin@inphaautomac.lk'],
            [
                'name' => 'Administrator',
                // Default admin password (change in production). To use a custom password,
                // update the created user manually or adjust this seeder.
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        // Create sample roles if they don't exist
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee']);
        $viewerRole = Role::firstOrCreate(['name' => 'viewer']);

        // Create Manager User (idempotent)
        $manager = User::firstOrCreate(
            ['email' => 'manager@inphaautomac.lk'],
            [
                'name' => 'John Manager',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $manager->assignRole('manager');

        // Create Employee User (idempotent)
        $employee = User::firstOrCreate(
            ['email' => 'employee@inphaautomac.lk'],
            [
                'name' => 'Jane Employee',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $employee->assignRole('employee');

        // Create Viewer User (idempotent)
        $viewer = User::firstOrCreate(
            ['email' => 'viewer@inphaautomac.lk'],
            [
                'name' => 'Bob Viewer',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $viewer->assignRole('viewer');

        // Create a user with multiple roles (idempotent)
        $multiRole = User::firstOrCreate(
            ['email' => 'multirole@inphaautomac.lk'],
            [
                'name' => 'Alice Multi-Role',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $multiRole->assignRole(['manager', 'employee']);

        $this->command->info('Sample users created successfully!');
        $this->command->table(
            ['Name', 'Email', 'Roles'],
            [
                [$admin->name, $admin->email, 'admin'],
                [$manager->name, $manager->email, 'manager'],
                [$employee->name, $employee->email, 'employee'],
                [$viewer->name, $viewer->email, 'viewer'],
                [$multiRole->name, $multiRole->email, 'manager, employee'],
            ]
        );
    }
}