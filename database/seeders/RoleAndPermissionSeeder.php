<?php

namespace Database\Seeders;

use App\Models\Owner;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Изчистване на кеша
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // ========================================
        // 1. Дефиниране на правата
        // ========================================
        
        $permissions = [
            // Продукти
            'view_products',
            'create_products',
            'edit_products',
            'delete_products',
            
            // Наличности
            'view_stocks',
            'adjust_stocks',
            
            // Продажби
            'view_sales',
            'create_sales',
            'edit_sales',
            'delete_sales',
            
            // Клиенти
            'view_clients',
            'create_clients',
            'edit_clients',
            'delete_clients',
            
            // Обекти
            'view_objects',
            'create_objects',
            'edit_objects',
            'delete_objects',
            
            // Потребители
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            
            // Фактури
            'view_invoices',
            'create_invoices',
            'edit_invoices',
            'delete_invoices',
            
            // Доклади
            'view_reports',
            'export_reports',
            
            // Системни
            'manage_settings',
            'view_all_carts', // Вижда всички активни колички
        ];
        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
        
        // ========================================
        // 2. Дефиниране на ролите
        // ========================================
        
        // Супер администратор (всички права)
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdminRole->givePermissionTo(Permission::all());
        
        // Собственик (всичко за неговата фирма)
        $ownerRole = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $ownerRole->givePermissionTo([
            'view_products', 'create_products', 'edit_products', 'delete_products',
            'view_stocks', 'adjust_stocks',
            'view_sales', 'create_sales', 'edit_sales', 'delete_sales',
            'view_clients', 'create_clients', 'edit_clients', 'delete_clients',
            'view_objects', 'create_objects', 'edit_objects', 'delete_objects',
            'view_users', 'create_users', 'edit_users', 'delete_users',
            'view_invoices', 'create_invoices', 'edit_invoices', 'delete_invoices',
            'view_reports', 'export_reports',
            'view_all_carts',
        ]);
        
        // Управител на обект
        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $managerRole->givePermissionTo([
            'view_products', 'create_products', 'edit_products',
            'view_stocks', 'adjust_stocks',
            'view_sales', 'create_sales',
            'view_clients', 'create_clients', 'edit_clients',
            'view_objects',
            'view_invoices', 'create_invoices',
            'view_reports',
            'view_all_carts',
        ]);
        
        // Касиер
        $cashierRole = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);
        $cashierRole->givePermissionTo([
            'view_products',
            'view_stocks',
            'create_sales',
            'view_clients', 'create_clients',
            'view_invoices',
        ]);
        
        // ========================================
        // 3. Създаване на тестови потребители
        // ========================================
        
        // Вземане на първия собственик
        $owner = Owner::first();
        $ownerId = $owner?->id ?? 1;
        
        // Супер администратор
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'owner_id' => $ownerId,
            ]
        );
        $superAdmin->assignRole('super_admin');
        
        // Собственик
        $ownerUser = User::firstOrCreate(
            ['email' => 'owner@example.com'],
            [
                'name' => 'Owner User',
                'password' => bcrypt('password'),
                'owner_id' => $ownerId,
            ]
        );
        $ownerUser->assignRole('owner');
        
        // Управител на обект
        $manager = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Store Manager',
                'password' => bcrypt('password'),
                'owner_id' => $ownerId,
            ]
        );
        $manager->assignRole('manager');
        
        // Касиер
        $cashier = User::firstOrCreate(
            ['email' => 'cashier@example.com'],
            [
                'name' => 'Cashier User',
                'password' => bcrypt('password'),
                'owner_id' => $ownerId,
            ]
        );
        $cashier->assignRole('cashier');
        
        $this->command->info('✅ Роли и права са създадени успешно!');
        $this->command->info('📋 Потребители:');
        $this->command->info('   superadmin@example.com / password (super_admin)');
        $this->command->info('   owner@example.com / password (owner)');
        $this->command->info('   manager@example.com / password (manager)');
        $this->command->info('   cashier@example.com / password (cashier)');
    }
}