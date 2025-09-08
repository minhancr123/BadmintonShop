<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Display reviews for a product
     */
    public function index(Product $product, Request $request)
    {
        $rating = $request->get('rating');
        $sortBy = $request->get('sort', 'newest');

        $query = $product->reviews()->approved()->with('user');

        // Filter by rating if specified
        if ($rating && $rating >= 1 && $rating <= 5) {
            $query->where('rating', $rating);
        }

        // Sort reviews
        switch ($sortBy) {
            case 'oldest':
                $query->oldest();
                break;
            case 'helpful':
                $query->orderBy('helpful_count', 'desc');
                break;
            case 'rating_high':
                $query->orderBy('rating', 'desc');
                break;
            case 'rating_low':
                $query->orderBy('rating', 'asc');
                break;
            case 'newest':
            default:
                $query->latest();
                break;
        }

        $reviews = $query->paginate(10);

        // Get review statistics
        $reviewStats = $this->getRatingStatistics($product);

        // Check if current user has reviewed this product
        $userReview = Auth::check() ? 
            Review::where('product_id', $product->id)
                ->where('user_id', Auth::id())
                ->first() : null;

        return view('products.reviews', compact('product', 'reviews', 'reviewStats', 'userReview', 'rating', 'sortBy'));
    }

    /**
     * Show form to create a review
     */
    public function create(Product $product)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để viết đánh giá');
        }

        // Check if user already reviewed this product
        $existingReview = Review::where('product_id', $product->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingReview) {
            return redirect()->route('products.show', $product->slug)
                ->with('error', 'Bạn đã đánh giá sản phẩm này rồi');
        }

        // Check if user has purchased this product
        $hasPurchased = $this->userHasPurchased($product, Auth::id());

        return view('reviews.create', compact('product', 'hasPurchased'));
    }

    /**
     * Store a new review
     */
    public function store(Request $request, Product $product)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để viết đánh giá');
        }

        // Check if user already reviewed this product
        $existingReview = Review::where('product_id', $product->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingReview) {
            return redirect()->route('products.show', $product->slug)
                ->with('error', 'Bạn đã đánh giá sản phẩm này rồi');
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:1000',
            'pros' => 'nullable|array|max:5',
            'pros.*' => 'string|max:255',
            'cons' => 'nullable|array|max:5',
            'cons.*' => 'string|max:255',
        ], [
            'rating.required' => 'Vui lòng chọn số sao đánh giá',
            'rating.min' => 'Đánh giá tối thiểu 1 sao',
            'rating.max' => 'Đánh giá tối đa 5 sao',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check if user has purchased this product
        $hasPurchased = $this->userHasPurchased($product, Auth::id());

        // Filter out empty pros and cons
        $pros = array_filter($request->pros ?? [], function($item) {
            return !empty(trim($item));
        });
        
        $cons = array_filter($request->cons ?? [], function($item) {
            return !empty(trim($item));
        });

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => Auth::id(),
            'rating' => $request->rating,
            'title' => $request->title,
            'comment' => $request->comment,
            'pros' => array_values($pros), // Re-index array
            'cons' => array_values($cons), // Re-index array
            'is_verified_purchase' => $hasPurchased,
            'is_approved' => true, // Auto-approve for now
        ]);

        return redirect()->route('products.show', $product->slug)
            ->with('success', 'Cảm ơn bạn đã đánh giá sản phẩm!');
    }

    /**
     * Get rating statistics for a product
     */
    private function getRatingStatistics(Product $product)
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

    /**
     * Check if user has purchased the product
     */
    private function userHasPurchased(Product $product, $userId)
    {
        return Order::where('user_id', $userId)
            ->where('status', 'delivered')
            ->whereHas('orderItems', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->exists();
    }
}
