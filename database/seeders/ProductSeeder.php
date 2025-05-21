<?php

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
            // CategoryID = 1: Mac
            [
                'CategoryID' => 1,
                'ProductName' => 'MacBook Pro 16"',
                'Description' => 'Laptop hiệu năng cao cho công việc chuyên nghiệp',
                'Price' => 2499,
                'Stock' => 30,
                'ImageURL' => 'macbookpro16.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'CategoryID' => 1,
                'ProductName' => 'MacBook Air',
                'Description' => 'Laptop nhẹ, pin lâu, hiệu năng ổn định',
                'Price' => 999,
                'Stock' => 80,
                'ImageURL' => 'macbookair.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // CategoryID = 2: iPhone
            [
                'CategoryID' => 2,
                'ProductName' => 'iPhone 14 Pro',
                'Description' => 'Điện thoại cao cấp với camera siêu nét',
                'Price' => 1099,
                'Stock' => 100,
                'ImageURL' => 'iphone14pro.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'CategoryID' => 2,
                'ProductName' => 'iPhone 14',
                'Description' => 'Phiên bản tiêu chuẩn của iPhone 14',
                'Price' => 799,
                'Stock' => 120,
                'ImageURL' => 'iphone14.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // CategoryID = 3: AirPods
            [
                'CategoryID' => 3,
                'ProductName' => 'AirPods Pro',
                'Description' => 'Tai nghe không dây chống ồn chủ động',
                'Price' => 249,
                'Stock' => 200,
                'ImageURL' => 'airpodspro.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'CategoryID' => 3,
                'ProductName' => 'AirPods 3',
                'Description' => 'Tai nghe không dây với âm thanh không gian',
                'Price' => 179,
                'Stock' => 150,
                'ImageURL' => 'airpods3.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // CategoryID = 4: Watch
            [
                'CategoryID' => 4,
                'ProductName' => 'Apple Watch Series 8',
                'Description' => 'Đồng hồ thông minh với nhiều tính năng sức khỏe',
                'Price' => 399,
                'Stock' => 150,
                'ImageURL' => 'applewatch8.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'CategoryID' => 4,
                'ProductName' => 'Apple Watch SE',
                'Description' => 'Phiên bản giá rẻ của Apple Watch',
                'Price' => 249,
                'Stock' => 180,
                'ImageURL' => 'applewatchse.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('products')->insert($products);
    }
}
