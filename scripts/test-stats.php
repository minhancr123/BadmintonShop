<?php

use App\Http\Controllers\ReviewController;
use App\Models\Product;

$product = Product::first();
$controller = new ReviewController();

// Call the private method using reflection
$reflection = new ReflectionClass($controller);
$method = $reflection->getMethod('getRatingStatistics');
$method->setAccessible(true);

$stats = $method->invoke($controller, $product);

echo "Product: " . $product->name . "\n";
echo "Stats from controller:\n";
print_r($stats);
