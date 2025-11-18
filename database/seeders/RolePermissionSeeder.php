<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
 app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ====== PERMISSIONS UNTUK ADMIN GUARD ======
        $adminPermissions = [
            // User Management
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',

            // Product Management
            'view_products',
            'create_products',
            'edit_products',
            'delete_products',

            // Order Management
            'view_orders',
            'create_orders',
            'edit_orders',
            'delete_orders',

            // Reports & Settings
            'view_reports',
            'manage_settings',

            // Role & Permission Management
            'view_roles',
            'create_roles',
            'edit_roles',
            'delete_roles',
        ];

        foreach ($adminPermissions as $permission) {
            Permission::create([
                'name' => $permission,
                'guard_name' => 'admin'
            ]);
        }

        // ====== ROLES UNTUK ADMIN ======

        // Super Admin - Full Access
        $superAdmin = Role::create([
            'name' => 'super-admin',
            'guard_name' => 'admin'
        ]);
        $superAdmin->givePermissionTo(Permission::where('guard_name', 'admin')->get());

        // Admin - Most Access
        $admin = Role::create([
            'name' => 'admin',
            'guard_name' => 'admin'
        ]);
        $admin->givePermissionTo([
            'view_users',
            'create_users',
            'edit_users',
            'view_products',
            'create_products',
            'edit_products',
            'delete_products',
            'view_orders',
            'create_orders',
            'edit_orders',
            'view_reports',
        ]);

        // Staff - Limited Access
        $staff = Role::create([
            'name' => 'staff',
            'guard_name' => 'admin'
        ]);
        $staff->givePermissionTo([
            'view_products',
            'view_orders',
            'edit_orders',
        ]);

        // ====== BUAT ADMIN USERS ======

        $superAdminUser = Admin::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@admin.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $superAdminUser->assignRole('super-admin');

        $adminUser = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $adminUser->assignRole('admin');

        $staffUser = Admin::create([
            'name' => 'Staff',
            'email' => 'staff@admin.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $staffUser->assignRole('staff');

        // ====== PERMISSIONS & ROLES UNTUK CUSTOMER (WEB GUARD) ======

        $customerPermissions = [
            'view_own_orders',
            'create_orders',
            'cancel_own_orders',
        ];

        foreach ($customerPermissions as $permission) {
            Permission::create([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        $customer = Role::create([
            'name' => 'customer',
            'guard_name' => 'web'
        ]);
        $customer->givePermissionTo(Permission::where('guard_name', 'web')->get());

        echo "✅ Roles & Permissions created successfully!\n";
        echo "Super Admin: superadmin@admin.com / password\n";
        echo "Admin: admin@admin.com / password\n";
        echo "Staff: staff@admin.com / password\n";
    }
}
