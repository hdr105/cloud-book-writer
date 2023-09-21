<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sections and subsections with at least 10 levels of nesting for each book
        for ($bookId = 1; $bookId <= 10; $bookId++) {
            $this->createSectionsAndSubsections($bookId, null, 1, 10);
        }
    }

    private function createSectionsAndSubsections($bookId, $parentId, $level, $maxLevels)
    {
        if ($level > $maxLevels) {
            return;
        }

        $sectionTitle = "Section $level of Book $bookId";
        $sectionDescription = "Section $level of Book $bookId Description";

        DB::table('sections')->insert([
            'title' => $sectionTitle,
            'description' => $sectionDescription,
            'book_id' => $bookId,
            'parent_id' => $parentId,
        ]);

        // Recursively create subsections
        $this->createSectionsAndSubsections($bookId, DB::getPdo()->lastInsertId(), $level + 1, $maxLevels);
    }
}
