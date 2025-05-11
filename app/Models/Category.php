<?php
// app/Models/Category.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    /**
     * Tên bảng trong cơ sở dữ liệu
     *
     * @var string
     */
    protected $table = 'categories';

    /**
     * Khóa chính của bảng
     *
     * @var string
     */
    protected $primaryKey = 'CategoryID';

    /**
     * Các thuộc tính có thể gán hàng loạt
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'CategoryName',
        'Description'
    ];

    /**
     * Quan hệ với Products
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'CategoryID', 'CategoryID');
    }
}