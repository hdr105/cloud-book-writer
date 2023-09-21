<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            DB::table('books')->insert([
                'title' => "Book $i",
                'author_id' => 1, // Author's ID from the users table
                'description' => "Book $i description",
            ]);
        }
    }
}
