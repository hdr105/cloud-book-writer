<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Roles
        Role::create(['name' => 'Author']);
        Role::create(['name' => 'Collaborator']);

        // Create permissions for books
        Permission::create(['name' => 'create-books']);
        Permission::create(['name' => 'store-books']);
        Permission::create(['name' => 'view-books']);
        Permission::create(['name' => 'edit-books']);
        Permission::create(['name' => 'update-books']);
        Permission::create(['name' => 'show-books']);
        Permission::create(['name' => 'delete-books']);
        // Create permissions for sections
        Permission::create(['name' => 'create-sections']);
        Permission::create(['name' => 'store-sections']);
        Permission::create(['name' => 'view-sections']);
        Permission::create(['name' => 'edit-sections']);
        Permission::create(['name' => 'update-sections']);
        Permission::create(['name' => 'show-sections']);
        Permission::create(['name' => 'delete-sections']);

    }
}
