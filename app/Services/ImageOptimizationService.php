<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImageOptimizationService
{
    protected $manager;
    
    protected $sizes = [
        'thumbnail' => ['width' => 300, 'height' => 300],
        'medium' => ['width' => 600, 'height' => 600],
        'large' => ['width' => 1200, 'height' => 1200],
    ];

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Optimize and resize uploaded image
     */
    public function optimizeImage($file, $folder = 'products', $generateSizes = true)
    {
        if (!$file || !$file->isValid()) {
            return null;
        }

        // Generate unique filename
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = $folder . '/' . $filename;

        // Load and optimize original image
        $image = $this->manager->read($file);
        
        // Optimize quality while maintaining reasonable file size
        $optimizedImage = $this->compressImage($image, 85);
        
        // Save original optimized version
        Storage::disk('public')->put($path, $optimizedImage);

        $result = [
            'original' => $path,
            'sizes' => []
        ];

        // Generate different sizes if requested
        if ($generateSizes) {
            foreach ($this->sizes as $sizeName => $dimensions) {
                $sizedPath = $folder . '/' . $sizeName . '_' . $filename;
                
                $resizedImage = $this->manager->read($file);
                $resizedImage = $resizedImage->cover(
                    $dimensions['width'], 
                    $dimensions['height']
                );
                
                $compressedResized = $this->compressImage($resizedImage, 80);
                Storage::disk('public')->put($sizedPath, $compressedResized);
                
                $result['sizes'][$sizeName] = $sizedPath;
            }
        }

        return $result;
    }

    /**
     * Compress image while maintaining quality
     */
    private function compressImage($image, $quality = 85)
    {
        // Resize if image is too large
        if ($image->width() > 2000 || $image->height() > 2000) {
            $image = $image->scale(width: 2000, height: 2000);
        }

        // Apply compression
        return $image->encodeByExtension('jpg', quality: $quality);
    }

    /**
     * Delete image and all its variants
     */
    public function deleteImage($imagePath)
    {
        if (!$imagePath) return;

        // Delete original
        Storage::disk('public')->delete($imagePath);

        // Delete sized variants
        $filename = basename($imagePath);
        $folder = dirname($imagePath);
        
        foreach (array_keys($this->sizes) as $sizeName) {
            $sizedPath = $folder . '/' . $sizeName . '_' . $filename;
            Storage::disk('public')->delete($sizedPath);
        }
    }

    /**
     * Get image URL for specific size
     */
    public function getImageUrl($imagePath, $size = 'original')
    {
        if (!$imagePath) return null;

        if ($size === 'original') {
            return Storage::url($imagePath);
        }

        $filename = basename($imagePath);
        $folder = dirname($imagePath);
        $sizedPath = $folder . '/' . $size . '_' . $filename;

        if (Storage::disk('public')->exists($sizedPath)) {
            return Storage::url($sizedPath);
        }

        // Fallback to original if sized version doesn't exist
        return Storage::url($imagePath);
    }

    /**
     * Optimize existing images in bulk
     */
    public function bulkOptimize($folder = 'products')
    {
        $files = Storage::disk('public')->files($folder);
        $processed = 0;

        foreach ($files as $file) {
            $fullPath = storage_path('app/public/' . $file);
            
            if ($this->isImage($fullPath)) {
                try {
                    $image = $this->manager->read($fullPath);
                    $optimized = $this->compressImage($image, 85);
                    Storage::disk('public')->put($file, $optimized);
                    $processed++;
                } catch (\Exception $e) {
                    // Log error but continue
                    Log::error("Failed to optimize image: " . $file . " - " . $e->getMessage());
                }
            }
        }

        return $processed;
    }

    /**
     * Check if file is an image
     */
    private function isImage($filePath)
    {
        if (!file_exists($filePath)) {
            return false;
        }
        
        $imageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $mimeType = mime_content_type($filePath);
        
        return in_array($mimeType, $imageTypes);
    }

    /**
     * Convert image to WebP format for better compression
     */
    public function convertToWebP($imagePath)
    {
        if (!Storage::disk('public')->exists($imagePath)) {
            return null;
        }

        $fullPath = storage_path('app/public/' . $imagePath);
        $image = $this->manager->read($fullPath);
        $webpPath = str_replace(['.jpg', '.jpeg', '.png'], '.webp', $imagePath);
        
        $webpImage = $image->encodeByExtension('webp', quality: 85);
        Storage::disk('public')->put($webpPath, $webpImage);
        
        return $webpPath;
    }
}
