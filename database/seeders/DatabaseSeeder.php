<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $seeders = [
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
            BookSeeder::class,
            SectionSeeder::class,
            // Add more seeders here in the desired order
        ];

        // Loop through the seeders and call them
        foreach ($seeders as $seeder) {
            $this->call($seeder);
        }
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
