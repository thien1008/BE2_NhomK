<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\OrderDetail;

class Order extends Model
{
    use HasFactory;

    protected $primaryKey = 'OrderID';

    protected $fillable = [
        'UserID',
        'TotalPrice',
        'Status',
        'version',
    ];

    public function items()
    {
        return $this->hasMany(OrderDetail::class, 'OrderID', 'OrderID');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }

    public static function createOrder(array $data)
    {
        return self::create([
            'UserID' => $data['UserID'],
            'TotalPrice' => $data['total'],
            'Status' => 'Pending',
            'version' => 1,
        ]);
    }

    public static function getLatestOrder($userId)
    {
        return self::where('UserID', $userId)
            ->orderBy('created_at', 'desc')
            ->with('items')
            ->first();
    }

    public static function getUserOrders($userId)
    {
        return self::where('UserID', $userId)
            ->with('items')
            ->orderBy('CreatedAt', 'desc')
            ->get();
    }
}