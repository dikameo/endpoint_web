<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;
use App\Models\Profile;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Use 'sanctum' guard to match with auth:sanctum middleware
        $guard = 'sanctum';

        // Create permissions for each resource
        $permissions = [
            // Product permissions
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            
            // Order permissions
            'orders.view',
            'orders.view_all',
            'orders.create',
            'orders.edit',
            'orders.delete',
            
            // User permissions
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            
            // Profile permissions
            'profiles.view',
            'profiles.edit',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => $guard]);
        }

        $this->command->info('Permissions created successfully!');

        // Create roles and assign permissions
        
        // Admin role - can manage everything
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);
        $adminRole->syncPermissions(Permission::where('guard_name', $guard)->get());
        $this->command->info('Admin role created with all permissions!');

        // User/Customer role - limited permissions
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => $guard]);
        $userRole->syncPermissions(
            Permission::where('guard_name', $guard)
                ->whereIn('name', [
                    'products.view',
                    'orders.view',
                    'orders.create',
                    'profiles.view',
                    'profiles.edit',
                ])->get()
        );
        $this->command->info('User role created with limited permissions!');

        // Assign admin role to admin@roastmaster.com
        $adminUser = User::where('email', 'admin@roastmaster.com')->first();
        if ($adminUser) {
            // Insert directly to model_has_roles table
            DB::table('model_has_roles')->updateOrInsert(
                [
                    'role_id' => $adminRole->id,
                    'model_type' => User::class,
                    'model_id' => $adminUser->id,
                ],
                [
                    'role_id' => $adminRole->id,
                    'model_type' => User::class,
                    'model_id' => $adminUser->id,
                ]
            );
            
            // Also update profile role
            DB::table('profiles')
                ->where('user_id', $adminUser->id)
                ->update(['role' => 'admin', 'updated_at' => now()]);
            
            $this->command->info('Admin role assigned to admin@roastmaster.com');
        } else {
            $this->command->warn('User admin@roastmaster.com not found. Please create the user first.');
        }

        $this->command->info('Roles and permissions setup completed!');
    }
}


