<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $role1 = Role::Create(['name' => 'admin']);
        $role2 = Role::Create(['name' => 'Cliente']);

        //definimos algunos permisos 
        $permission1 = Permission::create(['name' => 'Gestión de ordenes']);
        $permission2 = Permission::create(['name' => 'Generar ordenes']);
    }
}
