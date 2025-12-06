<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Services\CartService;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    private CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function apply(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50',
        ]);

        $subtotal = $this->cartService->calculateSubtotal();

        if ($subtotal <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Giỏ hàng của bạn đang trống.',
            ], 422);
        }

        $code = strtoupper($request->input('code'));
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá không hợp lệ.',
            ], 404);
        }

        $error = $this->cartService->validateCoupon($coupon, $subtotal);

        if ($error) {
            return response()->json([
                'success' => false,
                'message' => $error,
            ], 422);
        }

        $discount = $this->cartService->calculateDiscount($coupon, $subtotal);
        $shipping = $this->cartService->calculateShipping($subtotal);
        $total = max(0, $subtotal + $shipping - $discount);

        $this->cartService->rememberCoupon($coupon);

        return response()->json([
            'success' => true,
            'message' => 'Áp dụng mã giảm giá thành công.',
            'summary' => [
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'discount' => $discount,
                'total' => $total,
                'coupon' => [
                    'code' => $coupon->code,
                    'description' => $coupon->description,
                    'type' => $coupon->discount_type,
                    'value' => $coupon->discount_value,
                    'max_discount_amount' => $coupon->max_discount_amount,
                ],
            ],
        ]);
    }

    public function remove()
    {
        $this->cartService->forgetCoupon();
        $summary = $this->cartService->getSummary();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa mã giảm giá.',
            'summary' => $summary,
        ]);
    }
}
