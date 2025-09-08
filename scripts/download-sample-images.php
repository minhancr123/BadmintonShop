<?php
/**
 * Sample Image Download Script for Badminton Shop
 * This script downloads sample images from placeholder services
 */

// Create scripts directory first if it doesn't exist
$scriptsDir = dirname(__FILE__);
if (!is_dir($scriptsDir)) {
    mkdir($scriptsDir, 0755, true);
}

// Image configuration
$imageConfig = [
    'products/rackets' => [
        'yonex-arcsaber-11.jpg' => 'https://via.placeholder.com/800x800/FF6B6B/FFFFFF?text=Yonex+Arcsaber+11',
        'victor-thruster-k-9900.jpg' => 'https://via.placeholder.com/800x800/4ECDC4/FFFFFF?text=Victor+TK+9900',
        'lining-woods-n90.jpg' => 'https://via.placeholder.com/800x800/45B7D1/FFFFFF?text=Li-ning+N90',
        'yonex-voltric-z-force-2.jpg' => 'https://via.placeholder.com/800x800/F7DC6F/000000?text=Yonex+VT+ZF2',
        'victor-jetspeed-s12.jpg' => 'https://via.placeholder.com/800x800/BB8FCE/FFFFFF?text=Victor+JS+S12',
    ],
    'products/shoes' => [
        'yonex-power-cushion-65z.jpg' => 'https://via.placeholder.com/800x800/FF9FF3/FFFFFF?text=Yonex+PC65Z',
        'victor-a362.jpg' => 'https://via.placeholder.com/800x800/54A0FF/FFFFFF?text=Victor+A362',
        'lining-aytn079.jpg' => 'https://via.placeholder.com/800x800/5F27CD/FFFFFF?text=Li-ning+AYTN079',
        'mizuno-wave-fang-ss.jpg' => 'https://via.placeholder.com/800x800/00D2D3/FFFFFF?text=Mizuno+Wave',
    ],
    'products/apparel' => [
        'yonex-polo-10298.jpg' => 'https://via.placeholder.com/800x800/FF3838/FFFFFF?text=Yonex+Polo',
        'victor-t-shirt-t-5000.jpg' => 'https://via.placeholder.com/800x800/2ED573/FFFFFF?text=Victor+T-5000',
        'lining-shorts-aapn006.jpg' => 'https://via.placeholder.com/800x800/1E90FF/FFFFFF?text=Li-ning+Shorts',
        'yonex-skirt-26048.jpg' => 'https://via.placeholder.com/800x800/FFB8B8/000000?text=Yonex+Skirt',
    ],
    'products/accessories' => [
        'yonex-bag-9829.jpg' => 'https://via.placeholder.com/800x800/2C2C54/FFFFFF?text=Yonex+Bag',
        'victor-grip-gr262.jpg' => 'https://via.placeholder.com/800x800/40407A/FFFFFF?text=Victor+Grip',
        'yonex-string-bg65.jpg' => 'https://via.placeholder.com/800x800/706FD3/FFFFFF?text=Yonex+BG65',
        'victor-wristband.jpg' => 'https://via.placeholder.com/800x800/FF5252/FFFFFF?text=Victor+Band',
    ],
    'products/shuttlecocks' => [
        'yonex-aerosensa-30.jpg' => 'https://via.placeholder.com/800x800/33D9B2/FFFFFF?text=Yonex+AS30',
        'victor-gold-no1.jpg' => 'https://via.placeholder.com/800x800/FFD700/000000?text=Victor+Gold',
        'rsl-classic-tourney.jpg' => 'https://via.placeholder.com/800x800/FF9AA2/000000?text=RSL+Classic',
    ],
];

// Storage path
$storagePath = dirname(__DIR__) . '/storage/app/public/';

echo "🏸 Badminton Shop - Sample Image Downloader\n";
echo "==========================================\n\n";

// Function to download image
function downloadImage($url, $destination) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $data !== false) {
        return file_put_contents($destination, $data);
    }
    
    return false;
}

// Download images
$totalImages = 0;
$downloadedImages = 0;

foreach ($imageConfig as $category => $images) {
    $categoryPath = $storagePath . $category;
    
    // Create directory if it doesn't exist
    if (!is_dir($categoryPath)) {
        mkdir($categoryPath, 0755, true);
    }
    
    echo "📁 Downloading images for: " . ucfirst(str_replace('products/', '', $category)) . "\n";
    
    foreach ($images as $filename => $url) {
        $totalImages++;
        $destination = $categoryPath . '/' . $filename;
        
        if (downloadImage($url, $destination)) {
            $downloadedImages++;
            echo "  ✅ Downloaded: $filename\n";
        } else {
            echo "  ❌ Failed: $filename\n";
        }
        
        // Small delay to be respectful to the placeholder service
        usleep(100000); // 0.1 second
    }
    
    echo "\n";
}

echo "📊 Summary:\n";
echo "Total images: $totalImages\n";
echo "Downloaded: $downloadedImages\n";
echo "Failed: " . ($totalImages - $downloadedImages) . "\n\n";

echo "🎯 Next Steps:\n";
echo "1. Replace placeholder images with real product images\n";
echo "2. Update your product seeder to reference these images\n";
echo "3. Run: php artisan db:seed --class=ProductSeeder\n";
echo "4. View images at: http://localhost:8000/storage/products/category/image.jpg\n\n";

echo "📝 Image Storage Locations:\n";
foreach ($imageConfig as $category => $images) {
    echo "- $category/\n";
    foreach (array_keys($images) as $filename) {
        echo "  → $filename\n";
    }
}
