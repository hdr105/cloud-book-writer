<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define user data in an array
        $userData = [
            [
                'name' => 'Author Name',
                'email' => 'author@example.com',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Collaborator Name',
                'email' => 'collaborator@example.com',
                'password' => Hash::make('password'),
            ],
        ];

        // Insert users into the 'users' table in a single query
        DB::table('users')->insert($userData);

        // Define roles and permissions
        $roles = [
            'Author' => ['create-books', 'store-books', 'view-books' , 'edit-books', 'show-books', 'update-books', 'delete-books', 'create-sections', 'store-sections', 'edit-sections', 'update-sections', 'view-sections', 'delete-sections'],
            'Collaborator' => ['view-books', 'show-books', 'view-sections', 'edit-sections', 'update-sections'],
        ];

        // Attach roles and permissions to users
        foreach ($roles as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();
        
            if ($roleName === 'Author') {
                $userEmail = 'author@example.com';
            } elseif ($roleName === 'Collaborator') {
                $userEmail = 'collaborator@example.com';
            } else {
                // Handle other roles or skip if not needed
                continue;
            }
        
            $user = User::where('email', $userEmail)->first();
            $user->assignRole($role);
        
            if ($permissions === Permission::all()) {
                $role->syncPermissions($permissions);
            } else {
                $role->givePermissionTo($permissions);
            }
        }
    }
}
