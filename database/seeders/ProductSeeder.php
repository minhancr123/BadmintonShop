<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // Vợt cầu lông (Category ID: 1)
            [
                'name' => 'Vợt Yonex Arcsaber 11 Pro',
                'slug' => 'vot-yonex-arcsaber-11-pro',
                'description' => 'Vợt cầu lông Yonex Arcsaber 11 Pro phiên bản mới nhất, thiết kế cân bằng, phù hợp với lối chơi toàn diện.',
                'price' => 4500000,
                'sale_price' => 4200000,
                'quantity' => 15,
                'sku' => 'YNX-ARC11P',
                'category_id' => 1,
                'brand' => 'Yonex',
                'weight' => '3U (85-89g)',
                'flex' => 'Medium',
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Vợt Victor Thruster K 9900',
                'slug' => 'vot-victor-thruster-k-9900',
                'description' => 'Vợt Victor Thruster K 9900 - vợt công thủ toàn diện, độ cứng trung bình, phù hợp người chơi trung cấp.',
                'price' => 3800000,
                'sale_price' => null,
                'quantity' => 20,
                'sku' => 'VIC-TK9900',
                'category_id' => 1,
                'brand' => 'Victor',
                'weight' => '3U (85-89g)',
                'flex' => 'Stiff',
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Vợt Lining Axforce 90 Dragon Max',
                'slug' => 'vot-lining-axforce-90-dragon-max',
                'description' => 'Vợt Lining Axforce 90 Dragon Max - thiết kế nặng đầu, tấn công mạnh mẽ, dành cho người chơi chuyên nghiệp.',
                'price' => 5200000,
                'sale_price' => 4900000,
                'quantity' => 10,
                'sku' => 'LIN-AXF90DM',
                'category_id' => 1,
                'brand' => 'Lining',
                'weight' => '4U (80-84g)',
                'flex' => 'Extra Stiff',
                'is_featured' => false,
                'is_active' => true,
            ],

            // Giày cầu lông (Category ID: 2)
            [
                'name' => 'Giày Yonex Power Cushion 65Z3',
                'slug' => 'giay-yonex-power-cushion-65z3',
                'description' => 'Giày cầu lông Yonex 65Z3 với công nghệ Power Cushion+ giúp hấp thụ sốc tốt, di chuyển nhanh và ổn định.',
                'price' => 3200000,
                'sale_price' => 2950000,
                'quantity' => 25,
                'sku' => 'YNX-PC65Z3',
                'category_id' => 2,
                'brand' => 'Yonex',
                'weight' => null,
                'flex' => null,
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Giày Victor A960 Ultra',
                'slug' => 'giay-victor-a960-ultra',
                'description' => 'Giày Victor A960 Ultra - độ bám cao, hỗ trợ di chuyển đa hướng, thiết kế thời trang.',
                'price' => 2500000,
                'sale_price' => null,
                'quantity' => 30,
                'sku' => 'VIC-A960U',
                'category_id' => 2,
                'brand' => 'Victor',
                'weight' => null,
                'flex' => null,
                'is_featured' => false,
                'is_active' => true,
            ],

            // Quả cầu lông (Category ID: 3)
            [
                'name' => 'Cầu lông Yonex AS-50 (Hộp 12 quả)',
                'slug' => 'cau-long-yonex-as-50',
                'description' => 'Cầu lông Yonex AS-50 - cầu lông thi đấu chuyên nghiệp, lông ngỗng tự nhiên, độ bền cao.',
                'price' => 580000,
                'sale_price' => null,
                'quantity' => 50,
                'sku' => 'YNX-AS50',
                'category_id' => 3,
                'brand' => 'Yonex',
                'weight' => null,
                'flex' => null,
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Cầu lông Victor Gold (Hộp 12 quả)',
                'slug' => 'cau-long-victor-gold',
                'description' => 'Cầu lông Victor Gold - cầu tập luyện chất lượng cao, phù hợp cho câu lạc bộ và người chơi phong trào.',
                'price' => 420000,
                'sale_price' => 400000,
                'quantity' => 100,
                'sku' => 'VIC-GOLD',
                'category_id' => 3,
                'brand' => 'Victor',
                'weight' => null,
                'flex' => null,
                'is_featured' => false,
                'is_active' => true,
            ],

            // Túi vợt (Category ID: 4)
            [
                'name' => 'Túi vợt Yonex BA92031WEX Pro',
                'slug' => 'tui-vot-yonex-ba92031wex-pro',
                'description' => 'Túi vợt Yonex BA92031WEX Pro - 3 ngăn chính, chứa được 9 vợt, nhiều ngăn phụ tiện dụng.',
                'price' => 1800000,
                'sale_price' => null,
                'quantity' => 20,
                'sku' => 'YNX-BA92031',
                'category_id' => 4,
                'brand' => 'Yonex',
                'weight' => null,
                'flex' => null,
                'is_featured' => false,
                'is_active' => true,
            ],

            // Phụ kiện (Category ID: 5)
            [
                'name' => 'Cước đan vợt Yonex BG65 (Cuộn 200m)',
                'slug' => 'cuoc-dan-vot-yonex-bg65',
                'description' => 'Cước đan vợt Yonex BG65 - độ bền cao, đàn hồi tốt, phù hợp mọi style chơi.',
                'price' => 1200000,
                'sale_price' => 1100000,
                'quantity' => 40,
                'sku' => 'YNX-BG65-200',
                'category_id' => 5,
                'brand' => 'Yonex',
                'weight' => null,
                'flex' => null,
                'is_featured' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Quấn cán Yonex AC102EX (Bộ 30 cuộn)',
                'slug' => 'quan-can-yonex-ac102ex',
                'description' => 'Quấn cán Yonex AC102EX - chất liệu PU cao cấp, thấm hút mồ hôi tốt, độ bám cao.',
                'price' => 450000,
                'sale_price' => null,
                'quantity' => 60,
                'sku' => 'YNX-AC102EX',
                'category_id' => 5,
                'brand' => 'Yonex',
                'weight' => null,
                'flex' => null,
                'is_featured' => false,
                'is_active' => true,
            ],

            // Quần áo (Category ID: 6)
            [
                'name' => 'Áo cầu lông Yonex Tournament 2024',
                'slug' => 'ao-cau-long-yonex-tournament-2024',
                'description' => 'Áo cầu lông Yonex Tournament 2024 - chất liệu polyester cao cấp, thoáng khí, thấm hút mồ hôi.',
                'price' => 650000,
                'sale_price' => 580000,
                'quantity' => 50,
                'sku' => 'YNX-T2024',
                'category_id' => 6,
                'brand' => 'Yonex',
                'weight' => null,
                'flex' => null,
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Quần short Victor R-30200',
                'slug' => 'quan-short-victor-r-30200',
                'description' => 'Quần short cầu lông Victor R-30200 - thiết kế năng động, co giãn 4 chiều, thoải mái vận động.',
                'price' => 450000,
                'sale_price' => null,
                'quantity' => 40,
                'sku' => 'VIC-R30200',
                'category_id' => 6,
                'brand' => 'Victor',
                'weight' => null,
                'flex' => null,
                'is_featured' => false,
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
