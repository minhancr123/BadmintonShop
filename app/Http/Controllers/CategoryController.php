<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index()
    {
        $categories = Category::active()
                             ->withCount(['products' => function ($query) {
                                 $query->active();
                             }])
                             ->with(['products' => function ($query) {
                                 $query->active();
                             }])
                             ->orderBy('name')
                             ->get();
        
        // Popular categories (categories with most products)
        $popularCategories = Category::active()
                                   ->withCount(['products' => function ($query) {
                                       $query->active();
                                   }])
                                   ->orderBy('products_count', 'desc')
                                   ->limit(3)
                                   ->get();
        
        // Featured products from various categories
        $featuredProducts = Product::active()
                                  ->featured()
                                  ->with('category')
                                  ->limit(4)
                                  ->get();
        
        return view('categories.index', compact('categories', 'popularCategories', 'featuredProducts'));
    }

    /**
     * Display the specified category with its products.
     */
    public function show(Request $request, $slug)
    {
        $category = Category::with(['products' => function ($query) {
                                    $query->active();
                                }])
                           ->where('slug', $slug)
                           ->where('is_active', true)
                           ->firstOrFail();

        $query = $category->products()->active();

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

        // Brand filter
        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        // Price filter
        if ($request->filled('min_price')) {
            $minPrice = $request->min_price;
            $query->where(function($q) use ($minPrice) {
                $q->where(function($subQuery) use ($minPrice) {
                    $subQuery->whereNotNull('sale_price')
                             ->whereColumn('sale_price', '<', 'price')
                             ->where('sale_price', '>=', $minPrice);
                })->orWhere(function($subQuery) use ($minPrice) {
                    $subQuery->whereNull('sale_price')
                             ->where('price', '>=', $minPrice);
                })->orWhere(function($subQuery) use ($minPrice) {
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
                    $subQuery->whereNotNull('sale_price')
                             ->whereColumn('sale_price', '<', 'price')
                             ->where('sale_price', '<=', $maxPrice);
                })->orWhere(function($subQuery) use ($maxPrice) {
                    $subQuery->whereNull('sale_price')
                             ->where('price', '<=', $maxPrice);
                })->orWhere(function($subQuery) use ($maxPrice) {
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

        return view('categories.show', compact('category', 'products'));
    }

    /**
     * Show the form for creating a new category (Admin only).
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * Store a newly created category (Admin only).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Generate slug from name
        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        Category::create($validated);

        return redirect()->route('admin.categories.index')
                        ->with('success', 'Category created successfully.');
    }

    /**
     * Show the form for editing the category (Admin only).
     */
    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified category (Admin only).
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Update slug if name changed
        if ($validated['name'] !== $category->name) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($validated);

        return redirect()->route('admin.categories.index')
                        ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category (Admin only).
     */
    public function destroy($id)
    {
        try {
            $category = Category::findOrFail($id);
            
            // Check if category has products
            if ($category->products()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa danh mục có chứa sản phẩm!'
                ], 400);
            }
            
            // Delete category image if exists
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            
            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Danh mục đã được xóa thành công!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa danh mục: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle bulk actions for categories (Admin only).
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'categories' => 'required|array|min:1',
            'categories.*' => 'exists:categories,id'
        ]);

        $action = $request->action;
        $categoryIds = $request->categories;
        $categories = Category::whereIn('id', $categoryIds)->get();

        try {
            switch ($action) {
                case 'activate':
                    Category::whereIn('id', $categoryIds)->update(['is_active' => true]);
                    $message = "Đã kích hoạt {$categories->count()} danh mục thành công!";
                    break;

                case 'deactivate':
                    Category::whereIn('id', $categoryIds)->update(['is_active' => false]);
                    $message = "Đã vô hiệu hóa {$categories->count()} danh mục thành công!";
                    break;

                case 'delete':
                    // Check if any category has products
                    $categoriesWithProducts = $categories->filter(function ($category) {
                        return $category->products()->count() > 0;
                    });

                    if ($categoriesWithProducts->count() > 0) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Không thể xóa danh mục có chứa sản phẩm!'
                        ], 400);
                    }
                    
                    // Delete category images
                    foreach ($categories as $category) {
                        if ($category->image) {
                            Storage::disk('public')->delete($category->image);
                        }
                    }
                    
                    Category::whereIn('id', $categoryIds)->delete();
                    $message = "Đã xóa {$categories->count()} danh mục thành công!";
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
}
