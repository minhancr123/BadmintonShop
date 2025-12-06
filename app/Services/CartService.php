<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CartService
{
    /**
     * Get raw cart data from session.
     */
    public function getCart(): array
    {
        return session()->get('cart', []);
    }

    /**
     * Validate current cart against product availability and stock levels.
     */
    public function validateCartItems(?array $cart = null): array
    {
        $cartData = $cart ?? $this->getCart();
        $errors = [];

        foreach ($cartData as $id => $item) {
            $product = Product::find($id);

            if (!$product) {
                $errors[] = "Product '{$item['name']}' is no longer available.";
                continue;
            }

            if (!$product->is_active) {
                $errors[] = "Product '{$product->name}' is currently unavailable.";
                continue;
            }

            if ($product->quantity < $item['quantity']) {
                $errors[] = "Only {$product->quantity} units of '{$product->name}' are available, but you have {$item['quantity']} in your cart.";
                continue;
            }

            if ($product->quantity == 0) {
                $errors[] = "Product '{$product->name}' is out of stock.";
            }
        }

        return $errors;
    }

    /**
     * Build cart items with product instances.
     */
    public function getItems(): array
    {
        $items = [];

        foreach ($this->getCart() as $id => $item) {
            $product = Product::find($id);

            if ($product) {
                $items[] = [
                    'id' => $id,
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'price' => $product->current_price,
                    'subtotal' => $product->current_price * $item['quantity'],
                ];
            }
        }

        return $items;
    }

    /**
     * Calculate current cart subtotal.
     */
    public function calculateSubtotal(): float
    {
        return collect($this->getItems())->sum('subtotal');
    }

    /**
     * Determine shipping fee based on subtotal.
     */
    public function calculateShipping(float $subtotal): float
    {
        return $subtotal >= 500000 ? 0 : 30000;
    }

    /**
     * Retrieve coupon record stored in session if available.
     */
    public function getSessionCoupon(): ?Coupon
    {
        $data = session()->get('coupon');

        if (!$data) {
            return null;
        }

        return Coupon::where('code', $data['code'] ?? null)->first();
    }

    /**
     * Store coupon reference in session.
     */
    public function rememberCoupon(Coupon $coupon): void
    {
        session()->put('coupon', [
            'id' => $coupon->id,
            'code' => $coupon->code,
        ]);
    }

    /**
     * Forget coupon data from session.
     */
    public function forgetCoupon(): void
    {
        session()->forget('coupon');
    }

    /**
     * Validate coupon applicability for current user and subtotal.
     */
    public function validateCoupon(Coupon $coupon, float $subtotal): ?string
    {
        if (!$coupon->is_active) {
            return 'Mã giảm giá đã bị vô hiệu hóa.';
        }

        $now = now();

        if ($coupon->starts_at && $now->lt($coupon->starts_at)) {
            return 'Mã giảm giá chưa bắt đầu hiệu lực.';
        }

        if ($coupon->ends_at && $now->gt($coupon->ends_at)) {
            return 'Mã giảm giá đã hết hạn.';
        }

        if ($coupon->minimum_order_amount && $subtotal < $coupon->minimum_order_amount) {
            return 'Đơn hàng chưa đạt giá trị tối thiểu để áp dụng mã.';
        }

        if ($coupon->usage_limit && $coupon->usages()->count() >= $coupon->usage_limit) {
            return 'Mã giảm giá đã được sử dụng hết.';
        }

        if ($coupon->usage_limit_per_user) {
            $user = Auth::user();

            if (!$user) {
                return 'Vui lòng đăng nhập để sử dụng mã giảm giá này.';
            }

            $used = $coupon->usages()->where('user_id', $user->id)->count();

            if ($used >= $coupon->usage_limit_per_user) {
                return 'Bạn đã sử dụng hết số lần cho phép của mã này.';
            }
        }

        return null;
    }

    /**
     * Compute discount amount the coupon brings to current subtotal.
     */
    public function calculateDiscount(Coupon $coupon, float $subtotal): float
    {
        $discount = 0;

        if ($coupon->discount_type === 'fixed') {
            $discount = $coupon->discount_value;
        } elseif ($coupon->discount_type === 'percentage') {
            $discount = $subtotal * ($coupon->discount_value / 100);

            if ($coupon->max_discount_amount) {
                $discount = min($discount, $coupon->max_discount_amount);
            }
        }

        return (float) min($discount, $subtotal);
    }

    /**
     * Revalidate stored coupon against current cart.
     */
    public function revalidateCoupon(): ?string
    {
        $coupon = $this->getSessionCoupon();

        if (!$coupon) {
            return null;
        }

        $subtotal = $this->calculateSubtotal();
        $error = $this->validateCoupon($coupon, $subtotal);

        if ($error) {
            $this->forgetCoupon();
            return $error;
        }

        return null;
    }

    /**
     * Gather full cart summary with coupon and shipping considered.
     */
    public function getSummary(): array
    {
        $items = $this->getItems();
        $subtotal = collect($items)->sum('subtotal');
        $shipping = $this->calculateShipping($subtotal);
        $coupon = $this->getSessionCoupon();
        $discount = 0;
        $couponMeta = null;

        if ($coupon) {
            $error = $this->validateCoupon($coupon, $subtotal);

            if ($error) {
                $this->forgetCoupon();
            } else {
                $discount = $this->calculateDiscount($coupon, $subtotal);
                $couponMeta = [
                    'code' => $coupon->code,
                    'description' => $coupon->description,
                    'type' => $coupon->discount_type,
                    'value' => $coupon->discount_value,
                    'max_discount_amount' => $coupon->max_discount_amount,
                ];
            }
        }

        $total = max(0, $subtotal + $shipping - $discount);

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'discount' => $discount,
            'total' => $total,
            'coupon' => $couponMeta,
        ];
    }
}
