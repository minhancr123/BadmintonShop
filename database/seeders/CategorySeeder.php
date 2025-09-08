<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Vợt cầu lông',
                'slug' => 'vot-cau-long',
                'description' => 'Các loại vợt cầu lông chính hãng từ các thương hiệu nổi tiếng như Yonex, Victor, Lining',
                'is_active' => true,
            ],
            [
                'name' => 'Giày cầu lông',
                'slug' => 'giay-cau-long',
                'description' => 'Giày cầu lông chuyên nghiệp với độ bám tốt, hỗ trợ di chuyển nhanh trên sân',
                'is_active' => true,
            ],
            [
                'name' => 'Quả cầu lông',
                'slug' => 'qua-cau-long',
                'description' => 'Quả cầu lông các loại: cầu lông thi đấu, cầu lông tập luyện',
                'is_active' => true,
            ],
            [
                'name' => 'Túi vợt',
                'slug' => 'tui-vot',
                'description' => 'Túi đựng vợt cầu lông, balo cầu lông nhiều ngăn tiện dụng',
                'is_active' => true,
            ],
            [
                'name' => 'Phụ kiện',
                'slug' => 'phu-kien',
                'description' => 'Phụ kiện cầu lông: cước đan vợt, quấn cán, băng cổ tay, vớ thể thao',
                'is_active' => true,
            ],
            [
                'name' => 'Quần áo',
                'slug' => 'quan-ao',
                'description' => 'Quần áo cầu lông nam nữ, áo thun, quần short thể thao',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
