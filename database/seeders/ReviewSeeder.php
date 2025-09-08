<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $products = Product::all();

        if ($users->isEmpty() || $products->isEmpty()) {
            $this->command->info('No users or products found. Please run UserSeeder and ProductSeeder first.');
            return;
        }

        $reviews = [
            [
                'rating' => 5,
                'title' => 'Vợt tuyệt vời cho người mới chơi',
                'comment' => 'Vợt rất nhẹ, cầm rất êm tay. Phù hợp cho người mới bắt đầu học cầu lông. Giá cả hợp lý.',
                'pros' => ['Nhẹ', 'Cầm êm tay', 'Giá tốt', 'Thiết kế đẹp'],
                'cons' => ['Cần thời gian để làm quen'],
                'is_verified_purchase' => true,
            ],
            [
                'rating' => 4,
                'title' => 'Chất lượng ổn trong tầm giá',
                'comment' => 'Sản phẩm đúng như mô tả, giao hàng nhanh. Tuy nhiên độ bền chưa test được do mới mua.',
                'pros' => ['Đúng mô tả', 'Giao hàng nhanh', 'Đóng gói cẩn thận'],
                'cons' => ['Chưa biết về độ bền'],
                'is_verified_purchase' => true,
            ],
            [
                'rating' => 5,
                'title' => 'Rất hài lòng với sản phẩm',
                'comment' => 'Đã sử dụng được 2 tháng, vợt vẫn rất tốt. Recommend cho mọi người.',
                'pros' => ['Bền', 'Chất lượng tốt', 'Giá hợp lý'],
                'cons' => [],
                'is_verified_purchase' => true,
            ],
            [
                'rating' => 3,
                'title' => 'Bình thường, không có gì đặc biệt',
                'comment' => 'Sản phẩm ổn nhưng không xuất sắc. Có thể tìm được sản phẩm tương tự với giá tốt hơn.',
                'pros' => ['Chất lượng ổn'],
                'cons' => ['Giá hơi cao', 'Không có gì đặc biệt'],
                'is_verified_purchase' => false,
            ],
            [
                'rating' => 4,
                'title' => 'Good product for intermediate players',
                'comment' => 'The racket has good balance and control. Perfect for improving my game.',
                'pros' => ['Good balance', 'Nice control', 'Comfortable grip'],
                'cons' => ['A bit heavy for beginners'],
                'is_verified_purchase' => true,
            ],
        ];

        foreach ($products->take(10) as $product) {
            // Random number of reviews per product (0-3)
            $reviewCount = rand(0, 3);
            
            for ($i = 0; $i < $reviewCount; $i++) {
                $user = $users->random();
                $reviewData = $reviews[array_rand($reviews)];
                
                // Check if user already reviewed this product
                $existingReview = Review::where('product_id', $product->id)
                    ->where('user_id', $user->id)
                    ->first();
                
                if (!$existingReview) {
                    Review::create([
                        'product_id' => $product->id,
                        'user_id' => $user->id,
                        'rating' => $reviewData['rating'],
                        'title' => $reviewData['title'],
                        'comment' => $reviewData['comment'],
                        'pros' => $reviewData['pros'],
                        'cons' => $reviewData['cons'],
                        'is_verified_purchase' => $reviewData['is_verified_purchase'],
                        'is_approved' => true,
                        'helpful_count' => rand(0, 5),
                    ]);
                }
            }
        }
        
        $this->command->info('Reviews seeded successfully!');
    }
}
