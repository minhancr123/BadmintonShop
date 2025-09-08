<?php

use App\Models\Product;

// Update all products rating
$products = Product::all();

foreach ($products as $product) {
    $reviews = $product->reviews()->approved();
    $averageRating = $reviews->avg('rating') ?: 0;
    $reviewsCount = $reviews->count();
    
    $product->update([
        'average_rating' => round($averageRating, 1),
        'reviews_count' => $reviewsCount
    ]);
    
    echo "Updated {$product->name}: {$reviewsCount} reviews, {$averageRating} rating\n";
}

echo "All products updated!\n";
