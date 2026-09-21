<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Breaking News', 'Chhattisgarh', 'Raipur', 'Bhilai', 'Durg', 'Bilaspur',
            'Politics', 'Crime', 'Education', 'Business', 'Sports', 'Entertainment',
            'Technology', 'Health', 'Lifestyle', 'Photo News', 'Video News',
        ];

        foreach ($categories as $sortOrder => $name) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'status' => 'active', 'sort_order' => $sortOrder + 1],
            );
        }

        $this->command?->info(count($categories).' sample categories available.');
    }
}
