<?php

use App\Models\Product;

$product = Product::first();
echo "Product: " . $product->name . "\n";

$reviews = $product->reviews()->approved()->get();
echo "Total reviews: " . $reviews->count() . "\n";

echo "\nReview details:\n";
foreach($reviews as $review) {
    echo "Rating: " . $review->rating . " - User: " . $review->user->name . "\n";
}

echo "\nRating distribution:\n";
$distribution = [];
$total = $reviews->count();

for($i = 1; $i <= 5; $i++) {
    $count = $reviews->where('rating', $i)->count();
    $distribution[$i] = $count;
    $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
    echo $i . " stars: " . $count . " (" . $percentage . "%)\n";
}

echo "\nAverage rating: " . $reviews->avg('rating') . "\n";
