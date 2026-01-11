<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            ['name' => 'Bug', 'slug' => 'bug', 'color' => '#EF4444'], // Red
            ['name' => 'Feature', 'slug' => 'feature', 'color' => '#10B981'], // Green
            ['name' => 'Urgent', 'slug' => 'urgent', 'color' => '#F59E0B'], // Orange
            ['name' => 'Enhancement', 'slug' => 'enhancement', 'color' => '#3B82F6'], // Blue
            ['name' => 'Documentation', 'slug' => 'documentation', 'color' => '#6B7280'], // Gray
        ];

        foreach ($tags as $tag) {
            \App\Models\Tag::updateOrCreate(['slug' => $tag['slug']], $tag);
        }
    }
}
