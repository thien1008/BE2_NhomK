<?php
// database/seeders/CategorySeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'CategoryName' => 'Mac',
                'Description' => 'Dòng laptop của Apple',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'CategoryName' => 'iPhone',
                'Description' => 'Dòng điện thoại thông minh của Apple',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'CategoryName' => 'AirPods',
                'Description' => 'Tai nghe không dây của Apple',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'CategoryName' => 'Watch',
                'Description' => 'Đồng hồ thông minh của Apple',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('categories')->insert($categories);
    }
}
