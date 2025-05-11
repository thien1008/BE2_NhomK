<?php
// database/seeders/ProductSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'ProductName' => 'MacBook Air M2',
                'CategoryID' => 1,
                'Price' => 1199.99,
                'Stock' => 10,
                'Description' => 'Laptop siêu nhẹ, mạnh mẽ với chip M2.',
                'ImageURL' => 'macbook_air_m2.jpg',
                'CreatedAt' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'ProductName' => 'MacBook Pro 16',
                'CategoryID' => 1,
                'Price' => 2499.99,
                'Stock' => 5,
                'Description' => 'Dành cho dân chuyên nghiệp với màn hình Retina.',
                'ImageURL' => 'macbook_pro_16.jpg',
                'CreatedAt' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'ProductName' => 'iPhone 14 Pro',
                'CategoryID' => 2,
                'Price' => 1099.99,
                'Stock' => 15,
                'Description' => 'Smartphone cao cấp với camera Pro.',
                'ImageURL' => 'iphone_14_pro.jpg',
                'CreatedAt' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'ProductName' => 'iPhone SE 2022',
                'CategoryID' => 2,
                'Price' => 429.99,
                'Stock' => 20,
                'Description' => 'Giá rẻ nhưng mạnh mẽ với chip A15.',
                'ImageURL' => 'iphone_se_2022.jpg',
                'CreatedAt' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'ProductName' => 'Apple Watch Ultra',
                'CategoryID' => 4,
                'Price' => 799.99,
                'Stock' => 8,
                'Description' => 'Đồng hồ thông minh bền bỉ cho dân thể thao.',
                'ImageURL' => 'apple_watch_ultra.jpg',
                'CreatedAt' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'ProductName' => 'AirPods Pro 2',
                'CategoryID' => 3,
                'Price' => 249.99,
                'Stock' => 18,
                'Description' => 'Tai nghe chống ồn với chất lượng âm thanh tuyệt vời.',
                'ImageURL' => 'airpods_pro_2.jpg',
                'CreatedAt' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('products')->insert($products);
    }
}