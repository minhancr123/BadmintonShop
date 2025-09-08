<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $query = Product::with('category')->active();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Brand filter
        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        // Price filter - check both regular and sale prices
        if ($request->filled('min_price')) {
            $minPrice = $request->min_price;
            $query->where(function($q) use ($minPrice) {
                $q->where(function($subQuery) use ($minPrice) {
                    // Products on sale
                    $subQuery->whereNotNull('sale_price')
                             ->where('sale_price', '>=', $minPrice);
                })->orWhere(function($subQuery) use ($minPrice) {
                    // Regular products
                    $subQuery->whereNull('sale_price')
                             ->where('price', '>=', $minPrice);
                })->orWhere(function($subQuery) use ($minPrice) {
                    // Products on sale but sale_price is higher than regular price (invalid)
                    $subQuery->whereNotNull('sale_price')
                             ->whereColumn('sale_price', '>=', 'price')
                             ->where('price', '>=', $minPrice);
                });
            });
        }
        
        if ($request->filled('max_price')) {
            $maxPrice = $request->max_price;
            $query->where(function($q) use ($maxPrice) {
                $q->where(function($subQuery) use ($maxPrice) {
                    // Products on sale
                    $subQuery->whereNotNull('sale_price')
                             ->whereColumn('sale_price', '<', 'price')
                             ->where('sale_price', '<=', $maxPrice);
                })->orWhere(function($subQuery) use ($maxPrice) {
                    // Regular products
                    $subQuery->whereNull('sale_price')
                             ->where('price', '<=', $maxPrice);
                })->orWhere(function($subQuery) use ($maxPrice) {
                    // Products on sale but sale_price is higher than regular price (invalid)
                    $subQuery->whereNotNull('sale_price')
                             ->whereColumn('sale_price', '>=', 'price')
                             ->where('price', '<=', $maxPrice);
                });
            });
        }

        // On sale filter
        if ($request->filled('on_sale')) {
            $query->whereNotNull('sale_price')
                  ->whereColumn('sale_price', '<', 'price');
        }

        // Sorting
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'price_asc':
                $query->orderByRaw('CASE WHEN sale_price IS NOT NULL AND sale_price < price THEN sale_price ELSE price END ASC');
                break;
            case 'price_desc':
                $query->orderByRaw('CASE WHEN sale_price IS NOT NULL AND sale_price < price THEN sale_price ELSE price END DESC');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $products = $query->paginate(12);
        $categories = Category::active()->get();
        $brands = Product::active()->whereNotNull('brand')->distinct()->orderBy('brand')->pluck('brand');

        return view('products.index', compact('products', 'categories', 'brands'));
    }

    /**
     * Display the specified product.
     */
    public function show($slug)
    {
        $product = Product::with(['category', 'reviews' => function($query) {
            $query->approved()->with('user')->latest()->limit(5);
        }])->where('slug', $slug)->firstOrFail();
        
        $product->incrementViewCount();
        
        // Get rating statistics
        $reviewStats = $this->getReviewStats($product);
        
        // Check if current user has reviewed this product
        $userReview = null;
        if (auth()->check()) {
            $userReview = $product->reviews()->where('user_id', auth()->id())->first();
        }
        
        $relatedProducts = Product::where('category_id', $product->category_id)
                                  ->where('id', '!=', $product->id)
                                  ->active()
                                  ->limit(4)
                                  ->get();

        return view('products.show', compact('product', 'relatedProducts', 'reviewStats', 'userReview'));
    }

    /**
     * Show the form for creating a new product (Admin only).
     */
    public function create()
    {
        $categories = Category::active()->get();
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a newly created product (Admin only).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'quantity' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'brand' => 'nullable|string|max:255',
            'weight' => 'nullable|string|max:50',
            'flex' => 'nullable|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Generate slug from name
        $validated['slug'] = Str::slug($validated['name']);

        // Handle main image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        // Handle gallery images upload
        if ($request->hasFile('gallery')) {
            $galleryPaths = [];
            foreach ($request->file('gallery') as $file) {
                $galleryPaths[] = $file->store('products/gallery', 'public');
            }
            $validated['gallery'] = $galleryPaths;
        }

        Product::create($validated);

        return redirect()->route('admin.products.index')
                        ->with('success', 'Product created successfully.');
    }

    /**
     * Show the form for editing the product (Admin only).
     */
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::active()->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified product (Admin only).
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'quantity' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'brand' => 'nullable|string|max:255',
            'weight' => 'nullable|string|max:50',
            'flex' => 'nullable|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Update slug if name changed
        if ($validated['name'] !== $product->name) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Handle main image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        // Handle gallery images upload
        if ($request->hasFile('gallery')) {
            // Delete old gallery images if exists
            if ($product->gallery && is_array($product->gallery)) {
                foreach ($product->gallery as $oldImage) {
                    if (Storage::disk('public')->exists($oldImage)) {
                        Storage::disk('public')->delete($oldImage);
                    }
                }
            }
            
            $galleryPaths = [];
            foreach ($request->file('gallery') as $file) {
                $galleryPaths[] = $file->store('products/gallery', 'public');
            }
            $validated['gallery'] = $galleryPaths;
        }

        $product->update($validated);

        return redirect()->route('admin.products.index')
                        ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified product (Admin only).
     */
    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            
            // Delete product images if they exist
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            
            if ($product->gallery) {
                foreach ($product->gallery as $galleryImage) {
                    Storage::disk('public')->delete($galleryImage);
                }
            }
            
            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Sản phẩm đã được xóa thành công!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa sản phẩm: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle bulk actions for products (Admin only).
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'products' => 'required|array|min:1',
            'products.*' => 'exists:products,id'
        ]);

        $action = $request->action;
        $productIds = $request->products;
        $products = Product::whereIn('id', $productIds)->get();

        try {
            switch ($action) {
                case 'activate':
                    Product::whereIn('id', $productIds)->update(['is_active' => true]);
                    $message = "Đã kích hoạt {$products->count()} sản phẩm thành công!";
                    break;

                case 'deactivate':
                    Product::whereIn('id', $productIds)->update(['is_active' => false]);
                    $message = "Đã vô hiệu hóa {$products->count()} sản phẩm thành công!";
                    break;

                case 'delete':
                    // Delete product images
                    foreach ($products as $product) {
                        if ($product->image) {
                            Storage::disk('public')->delete($product->image);
                        }
                        
                        if ($product->gallery) {
                            foreach ($product->gallery as $galleryImage) {
                                Storage::disk('public')->delete($galleryImage);
                            }
                        }
                    }
                    
                    Product::whereIn('id', $productIds)->delete();
                    $message = "Đã xóa {$products->count()} sản phẩm thành công!";
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi thực hiện thao tác: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get review statistics for a product
     */
    private function getReviewStats($product)
    {
        $reviews = $product->reviews()->approved();
        $totalReviews = $reviews->count();

        if ($totalReviews === 0) {
            return [
                'total' => 0,
                'average' => 0,
                'distribution' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0],
                'percentages' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0],
            ];
        }

        $averageRating = $reviews->avg('rating');
        
        // Get rating distribution
        $distribution = [];
        $percentages = [];
        
        for ($i = 5; $i >= 1; $i--) {
            $count = $product->reviews()->approved()->where('rating', $i)->count();
            $distribution[$i] = $count;
            $percentages[$i] = $totalReviews > 0 ? round(($count / $totalReviews) * 100, 1) : 0;
        }

        return [
            'total' => $totalReviews,
            'average' => round($averageRating, 1),
            'distribution' => $distribution,
            'percentages' => $percentages,
        ];
    }
}
