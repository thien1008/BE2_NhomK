<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $coupons = Coupon::where('Code', 'like', "%{$search}%")
            ->paginate(10);

        return view('admin.coupons.index', compact('coupons', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'Code' => 'required|string|max:50|unique:coupons',
            'DiscountPercentage' => 'required|numeric|min:0|max:100',
            'ValidFrom' => 'required|date',
            'ValidTo' => 'required|date|after_or_equal:ValidFrom',
            'UsageLimit' => 'nullable|integer|min:0',
            'UserLimit' => 'nullable|integer|min:0',
        ]);

        Coupon::create($request->only([
            'Code',
            'DiscountPercentage',
            'ValidFrom',
            'ValidTo',
            'UsageLimit',
            'UserLimit'
        ]));

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon created successfully.');
    }

    public function update(Request $request, Coupon $coupon)
    {
        $request->validate([
            'Code' => 'required|string|max:50|unique:coupons,Code,' . $coupon->CouponID . ',CouponID',
            'DiscountPercentage' => 'required|numeric|min:0|max:100',
            'ValidFrom' => 'required|date',
            'ValidTo' => 'required|date|after_or_equal:ValidFrom',
            'UsageLimit' => 'nullable|integer|min:0',
            'UserLimit' => 'nullable|integer|min:0',
        ]);

        $coupon->update($request->only([
            'Code',
            'DiscountPercentage',
            'ValidFrom',
            'ValidTo',
            'UsageLimit',
            'UserLimit'
        ]));

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon updated successfully.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon deleted successfully.');
    }
}