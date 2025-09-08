<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Show the application homepage.
     */
    public function index()
    {
        // Featured products
        $featuredProducts = Product::with('category')
                                  ->active()
                                  ->featured()
                                  ->limit(8)
                                  ->get();

        // Latest products
        $latestProducts = Product::with('category')
                                ->active()
                                ->orderBy('created_at', 'desc')
                                ->limit(8)
                                ->get();

        // Popular categories (categories with most products)
        $popularCategories = Category::active()
                                   ->withCount(['products' => function ($query) {
                                       $query->active();
                                   }])
                                   ->orderBy('products_count', 'desc')
                                   ->limit(6)
                                   ->get();

        // Products on sale
        $saleProducts = Product::with('category')
                              ->active()
                              ->whereNotNull('sale_price')
                              ->where('sale_price', '<', DB::raw('price'))
                              ->orderBy('created_at', 'desc')
                              ->limit(8)
                              ->get();

        // Statistics for homepage
        $stats = [
            'total_products' => Product::active()->count(),
            'total_categories' => Category::active()->count(),
            'products_on_sale' => Product::active()
                                        ->whereNotNull('sale_price')
                                        ->where('sale_price', '<', DB::raw('price'))
                                        ->count(),
        ];

        return view('welcome', compact(
            'featuredProducts',
            'latestProducts', 
            'popularCategories',
            'saleProducts',
            'stats'
        ));
    }

    /**
     * Search products.
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        if (empty($query)) {
            return redirect()->route('products.index');
        }

        $products = Product::with('category')
                          ->active()
                          ->where(function($q) use ($query) {
                              $q->where('name', 'like', "%{$query}%")
                                ->orWhere('description', 'like', "%{$query}%")
                                ->orWhere('brand', 'like', "%{$query}%")
                                ->orWhere('sku', 'like', "%{$query}%");
                          })
                          ->orderBy('name', 'asc')
                          ->paginate(12);

        $categories = Category::active()->get();

        return view('products.search', compact('products', 'categories', 'query'));
    }

    /**
     * Show about us page.
     */
    public function about()
    {
        return view('pages.about');
    }

    /**
     * Show contact page.
     */
    public function contact()
    {
        return view('pages.contact');
    }

    /**
     * Handle contact form submission.
     */
    public function contactSubmit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
        ]);

        // Here you would typically send email or save to database
        // For now, we'll just return success message
        
        return redirect()->back()
                        ->with('success', 'Thank you for contacting us! We will get back to you soon.');
    }

    /**
     * Show privacy policy page.
     */
    public function privacy()
    {
        return view('pages.privacy');
    }

    /**
     * Show terms and conditions page.
     */
    public function terms()
    {
        return view('pages.terms');
    }

    /**
     * Show FAQ page.
     */
    public function faq()
    {
        $faqs = [
            [
                'question' => 'How do I place an order?',
                'answer' => 'You can place an order by browsing our products, adding items to your cart, and proceeding to checkout. You will need to create an account or log in to complete your purchase.'
            ],
            [
                'question' => 'What payment methods do you accept?',
                'answer' => 'We accept Cash on Delivery (COD) and Bank Transfer. All payments are secure and encrypted.'
            ],
            [
                'question' => 'How long does shipping take?',
                'answer' => 'Standard shipping takes 2-3 business days within Ho Chi Minh City and 3-5 business days for other provinces in Vietnam.'
            ],
            [
                'question' => 'Can I return or exchange products?',
                'answer' => 'Yes, you can return or exchange products within 7 days of purchase, provided they are in original condition with tags attached.'
            ],
            [
                'question' => 'Do you offer racket stringing service?',
                'answer' => 'Yes, we offer professional racket stringing service. You can add this service when purchasing a racket or bring your racket to our store.'
            ],
            [
                'question' => 'Are your products authentic?',
                'answer' => 'Yes, all our products are 100% authentic and sourced directly from authorized distributors. We provide warranty for all branded products.'
            ],
        ];

        return view('pages.faq', compact('faqs'));
    }

    /**
     * API endpoint for getting product suggestions (for search autocomplete).
     */
    public function productSuggestions(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $products = Product::active()
                          ->where('name', 'like', "%{$query}%")
                          ->select('id', 'name', 'slug', 'price', 'sale_price', 'image')
                          ->limit(10)
                          ->get();

        return response()->json($products);
    }

    /**
     * Newsletter subscription.
     */
    public function newsletter(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        // Here you would typically save to newsletter list
        // For now, we'll just return success message
        
        return response()->json([
            'success' => true,
            'message' => 'Thank you for subscribing to our newsletter!'
        ]);
    }
}
