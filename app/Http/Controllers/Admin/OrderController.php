<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $orders = Order::with('user')
            ->whereHas('user', function ($query) use ($search) {
                $query->where('FullName', 'like', "%{$search}%");
            })
            ->paginate(10);

        return view('admin.orders.index', compact('orders', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'UserID' => 'required|exists:users,UserID',
            'TotalPrice' => 'required|numeric|min:0',
            'Status' => 'required|in:Pending,Completed,Cancelled',
        ]);

        Order::create($request->only(['UserID', 'TotalPrice', 'Status']));

        return redirect()->route('admin.orders.index')->with('success', 'Order created successfully.');
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'UserID' => 'required|exists:users,UserID',
            'TotalPrice' => 'required|numeric|min:0',
            'Status' => 'required|in:Pending,Completed,Cancelled',
        ]);

        $order->update($request->only(['UserID', 'TotalPrice', 'Status']));

        return redirect()->route('admin.orders.index')->with('success', 'Order updated successfully.');
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return redirect()->route('admin.orders.index')->with('success', 'Order deleted successfully.');
    }
}