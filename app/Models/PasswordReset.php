<?php
// app/Models/PasswordReset.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    use HasFactory;

    /**
     * Tên bảng trong cơ sở dữ liệu
     *
     * @var string
     */
    protected $table = 'password_resets';

    /**
     * Không sử dụng khóa chính tự tăng
     */
    public $incrementing = false;

    /**
     * Khóa chính của bảng
     *
     * @var string
     */
    protected $primaryKey = 'email';

    /**
     * Các thuộc tính có thể gán hàng loạt
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'token',
        'expires_at'
    ];
}