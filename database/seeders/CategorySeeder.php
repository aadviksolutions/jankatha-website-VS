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
            'Breaking News', 'Chhattisgarh', 'Raipur', 'Bilaspur', 'Durg', 'Bhilai',
            'Korba', 'Bastar', 'India', 'World', 'Politics', 'Business', 'Crime',
            'Education', 'Health', 'Sports', 'Technology', 'Entertainment',
            'Lifestyle', 'Photo News', 'Video News',
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
