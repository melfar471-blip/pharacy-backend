<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
    
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        
        $permissions = [
            'access pos',
            'view sales',
            'create returns',
            'manage medicines',
            'manage suppliers',
            'manage purchase orders',
            'view financial reports',
            'access admin dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

       
        $cashierRole = Role::findOrCreate('cashier');
        $cashierRole->givePermissionTo([
            'access pos',
            'create returns',
        ]);

        $adminRole = Role::findOrCreate('admin');
        $adminRole->givePermissionTo(Permission::all());

       
        $admin = User::firstOrCreate(
            ['email' => 'admin@pharmacy.com'],
            [
                'name' => 'مدير النظام',
                'password' => Hash::make('password123'),
            ]
        );
        $admin->assignRole($adminRole);

      
        $cashier = User::firstOrCreate(
            ['email' => 'cashier@pharmacy.com'],
            [
                'name' => 'كاشير الصيدلية',
                'password' => Hash::make('password123'),
            ]
        );
        $cashier->assignRole($cashierRole);
    }
}

?>
