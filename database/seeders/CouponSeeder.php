<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coupon;
use Illuminate\Support\Str;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'WELCOME10',
                'name' => 'Chào mừng khách hàng mới',
                'description' => 'Giảm 10% cho đơn hàng đầu tiên',
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'max_discount_amount' => 100000,
                'minimum_order_amount' => 500000,
                'usage_limit' => 100,
                'usage_limit_per_user' => 1,
                'is_active' => true,
                'starts_at' => now(),
                'ends_at' => now()->addMonths(3),
            ],
            [
                'code' => 'FREESHIP',
                'name' => 'Miễn phí vận chuyển',
                'description' => 'Giảm 30.000đ phí vận chuyển',
                'discount_type' => 'fixed',
                'discount_value' => 30000,
                'max_discount_amount' => null,
                'minimum_order_amount' => 300000,
                'usage_limit' => 500,
                'usage_limit_per_user' => 5,
                'is_active' => true,
                'starts_at' => now(),
                'ends_at' => now()->addMonths(1),
            ],
            [
                'code' => 'SALE20',
                'name' => 'Giảm 20%',
                'description' => 'Giảm 20% cho đơn hàng trên 1 triệu',
                'discount_type' => 'percentage',
                'discount_value' => 20,
                'max_discount_amount' => 500000,
                'minimum_order_amount' => 1000000,
                'usage_limit' => 50,
                'usage_limit_per_user' => 2,
                'is_active' => true,
                'starts_at' => now(),
                'ends_at' => now()->addWeeks(2),
            ],
            [
                'code' => 'VIP50K',
                'name' => 'Giảm 50K',
                'description' => 'Giảm ngay 50.000đ',
                'discount_type' => 'fixed',
                'discount_value' => 50000,
                'max_discount_amount' => null,
                'minimum_order_amount' => 200000,
                'usage_limit' => null, // Không giới hạn
                'usage_limit_per_user' => null, // Không giới hạn
                'is_active' => true,
                'starts_at' => now(),
                'ends_at' => now()->addMonths(6),
            ],
            [
                'code' => 'EXPIRED',
                'name' => 'Mã đã hết hạn',
                'description' => 'Mã này để test trường hợp hết hạn',
                'discount_type' => 'percentage',
                'discount_value' => 15,
                'max_discount_amount' => 200000,
                'minimum_order_amount' => 0,
                'usage_limit' => 10,
                'usage_limit_per_user' => 1,
                'is_active' => true,
                'starts_at' => now()->subMonths(2),
                'ends_at' => now()->subDays(1), // Đã hết hạn
            ],
            [
                'code' => 'INACTIVE',
                'name' => 'Mã đã vô hiệu hóa',
                'description' => 'Mã này để test trường hợp bị vô hiệu hóa',
                'discount_type' => 'percentage',
                'discount_value' => 25,
                'max_discount_amount' => 300000,
                'minimum_order_amount' => 0,
                'usage_limit' => 100,
                'usage_limit_per_user' => 1,
                'is_active' => false, // Đã vô hiệu hóa
                'starts_at' => now(),
                'ends_at' => now()->addMonths(1),
            ],
        ];

        foreach ($coupons as $couponData) {
            Coupon::create($couponData);
        }

        $this->command->info('Đã tạo ' . count($coupons) . ' mã giảm giá mẫu!');
    }
}
