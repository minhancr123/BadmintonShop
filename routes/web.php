<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SocialAuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Health check endpoint for Render
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toIso8601String(),
        'service' => 'BadmintonShop',
    ]);
});

// Debug session endpoint
Route::middleware('web')->get('/debug-session', function () {
    session()->put('test', 'value_' . time());
    $sessionId = session()->getId();
    $sessionPath = storage_path('framework/sessions');
    $sessionFile = $sessionPath . '/' . $sessionId;
    
    return response()->json([
        'session_id' => $sessionId,
        'session_driver' => config('session.driver'),
        'session_path' => $sessionPath,
        'session_file_exists' => file_exists($sessionFile),
        'session_files_count' => count(glob($sessionPath . '/*')),
        'session_data' => session()->all(),
        'cookies' => request()->cookies->all(),
        'is_https' => request()->isSecure(),
        'headers' => [
            'X-Forwarded-Proto' => request()->header('X-Forwarded-Proto'),
            'X-Forwarded-For' => request()->header('X-Forwarded-For'),
        ],
    ]);
});

// Check middleware stack
Route::get('/check-middleware', function () {
    $router = app('router');
    $route = $router->current();
    
    return response()->json([
        'middleware' => $route ? $route->gatherMiddleware() : [],
        'web_middleware' => config('app')::VERSION >= 11 ? 
            app(\Illuminate\Contracts\Http\Kernel::class)->getMiddlewareGroups()['web'] ?? [] :
            [],
        'cookie_jar' => app('cookie')->getQueuedCookies(),
    ]);
});

// Test raw cookie WITH web middleware
Route::middleware('web')->get('/test-raw-cookie', function () {
    $cookieValue = 'laravel_value_' . time();
    
    // Queue the cookie
    cookie()->queue('laravel_test_cookie', $cookieValue, 60, '/', null, false, true, false, 'lax');
    
    $response = response()->json([
        'message' => 'Testing cookie setting',
        'timestamp' => time(),
        'cookie_value' => $cookieValue,
        'queued_cookies' => count(app('cookie')->getQueuedCookies()),
    ]);
    
    // Also add cookie to response
    return $response->cookie('response_test_cookie', 'response_value_' . time(), 60, '/', null, false, true, false, 'lax');
});

// Test session save
Route::middleware('web')->get('/test-session-save', function () {
    try {
        // Check if session is working
        $before = session()->all();
        session()->put('test_key', 'test_value_' . time());
        session()->save();
        $after = session()->all();
        
        $sessionPath = storage_path('framework/sessions');
        $sessionId = session()->getId();
        $sessionFile = $sessionPath . '/' . $sessionId;
        
        $fileContents = null;
        if (file_exists($sessionFile)) {
            $fileContents = file_get_contents($sessionFile);
        }
        
        $response = response()->json([
            'success' => true,
            'session_id' => $sessionId,
            'session_driver' => config('session.driver'),
            'before' => $before,
            'after' => $after,
            'file_exists' => file_exists($sessionFile),
            'file_path' => $sessionFile,
            'file_contents' => $fileContents,
            'storage_writable' => is_writable($sessionPath),
            'middleware_loaded' => class_exists('Illuminate\Session\Middleware\StartSession'),
        ]);
        
        // Try to manually set cookie
        return $response->withCookie(cookie('test_cookie', 'test_value', 120, '/', null, false, true, false, 'lax'));
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => true,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
});

// Test force login
Route::middleware('web')->get('/test-login', function () {
    // Try to find any admin user
    $user = \App\Models\User::where('role', 'admin')->first();
    if (!$user) {
        // Try first user
        $user = \App\Models\User::first();
    }
    
    if ($user) {
        \Illuminate\Support\Facades\Auth::login($user);
        session()->regenerate();
        session()->save(); // Force save session
        
        $response = response()->json([
            'success' => true,
            'user_id' => auth()->id(),
            'user_email' => $user->email,
            'session_id' => session()->getId(),
            'is_authenticated' => auth()->check(),
            'session_name' => config('session.cookie'),
            'session_path' => config('session.path'),
            'session_domain' => config('session.domain'),
            'session_secure' => config('session.secure'),
            'session_same_site' => config('session.same_site'),
            'redirect_to' => '/dashboard',
        ]);
        
        // Manually set cookie to test
        $cookieName = config('session.cookie');
        $response->cookie($cookieName, session()->getId(), config('session.lifetime'));
        
        return $response;
    }
    
    // No users found, show all users
    $allUsers = \App\Models\User::select('id', 'name', 'email', 'role')->get();
    return response()->json([
        'error' => 'No users found', 
        'all_users' => $allUsers,
        'total_users' => $allUsers->count()
    ]);
});

// Test check auth
Route::middleware('web')->get('/test-auth', function () {
    return response()->json([
        'is_authenticated' => auth()->check(),
        'user_id' => auth()->id(),
        'user' => auth()->user(),
        'session_id' => session()->getId(),
        'session_data' => session()->all(),
    ]);
});

// Home page
Route::get('/', [HomeController::class, 'index'])->name('home');
// Change password
Route::middleware(['auth'])->group(function () {
    Route::post('/change-password', [ChangePasswordController::class, 'update'])->name('password.change');
});

// Additional pages
Route::get('/search', [HomeController::class, 'search'])->name('search');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'contactSubmit'])->name('contact.submit');
Route::get('/privacy', [HomeController::class, 'privacy'])->name('privacy');
Route::get('/terms', [HomeController::class, 'terms'])->name('terms');
Route::get('/faq', [HomeController::class, 'faq'])->name('faq');
Route::post('/newsletter', [HomeController::class, 'newsletter'])->name('newsletter');
Route::get('/api/product-suggestions', [HomeController::class, 'productSuggestions'])->name('api.product-suggestions');

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Product Routes (Public)
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

// Category Routes (Public)
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{slug}', [CategoryController::class, 'show'])->name('categories.show');

// User Routes (Authenticated)
Route::middleware(['auth'])->group(function () {
    // User Dashboard
    Route::get('/dashboard', function () {
        $orders = auth()->user()->orders()->latest()->limit(5)->get();
        return view('dashboard', compact('orders'));
    })->name('dashboard');

    // Cart Routes
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
    Route::get('/api/cart/count', [CartController::class, 'getCartCount'])->name('api.cart.count');

    // Order Routes
    Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/orders/{order}/reorder', [OrderController::class, 'reorder'])->name('orders.reorder');
    Route::get('/orders/{order}/invoice', [OrderController::class, 'downloadInvoice'])->name('orders.invoice');
});

// Admin Routes (Admin only)
Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
    // Admin Dashboard
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');

    // Product Management
    Route::get('/products', [AdminController::class, 'products'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products/bulk-action', [ProductController::class, 'bulkAction'])->name('products.bulk-action');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::post('/products/{id}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');

    // Category Management
    Route::get('/categories', [AdminController::class, 'categories'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories/bulk-action', [CategoryController::class, 'bulkAction'])->name('categories.bulk-action');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::post('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Order Management
    Route::get('/orders', [AdminController::class, 'orders'])->name('orders.index');
    Route::get('/orders/{order}', [AdminController::class, 'orderShow'])->name('orders.show');
    Route::patch('/orders/{order}/status', [AdminController::class, 'updateOrderStatus'])->name('orders.update-status');

    // User Management
    Route::get('/users', [AdminController::class, 'users'])->name('users.index');
    Route::get('/users/{user}', [AdminController::class, 'userShow'])->name('users.show');
    Route::patch('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.update-role');
    
    // Reports
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::get('/export/{type}', [AdminController::class, 'exportReport'])->name('admin.export');
});

// Payment Routes
Route::group(['prefix' => 'payment'], function () {
    Route::post('/process/{order}', [PaymentController::class, 'processPayment'])->name('payment.process');

    Route::post('/vnpay_payment', [OrderController::class, 'vnpay_payment']);
    Route::get('/vnpay/return', [OrderController::class, 'vnpayReturn'])->name('vnpay.return');

    Route::get('/momo_return', [OrderController::class, 'momoReturn'])->name('momo.return');
    Route::post('/momo_ipn', [OrderController::class, 'momoIpn'])->name('momo.ipn');
});

// Review Routes
Route::group(['prefix' => 'products/{product}'], function () {
    Route::get('/reviews', [ReviewController::class, 'index'])->name('products.reviews');
    Route::get('/reviews/create', [ReviewController::class, 'create'])->name('reviews.create')->middleware('auth');
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store')->middleware('auth');
});

Route::group(['prefix' => 'reviews', 'middleware' => 'auth'], function () {
    Route::post('/{review}/helpful', [ReviewController::class, 'helpful'])->name('reviews.helpful');
});

// Block and unblock user
Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
    Route::post('users/{id}/block', [AdminController::class, 'blockUser'])->name('users.block');
    Route::post('users/{id}/unblock', [AdminController::class, 'unblockUser'])->name('users.unblock');
    Route::post('users/{id}/reset-password', [AdminController::class, 'resetPassword'])->name('users.reset-password');
});

Route::get('auth/google', [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

// Debug Route
Route::get('/debug-session', function () {
    return [
        'session_id' => session()->getId(),
        'user_id' => auth()->id(),
        'is_secure' => request()->secure(),
        'ip' => request()->ip(),
        'user_agent' => request()->userAgent(),
        'headers' => request()->header(),
        'session_config' => config('session'),
    ];
});
