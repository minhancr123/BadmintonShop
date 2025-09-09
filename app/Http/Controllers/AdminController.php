<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Admin dashboard.
     */
    public function index()
    {
        // Statistics
        $stats = [
            'total_products' => Product::count(),
            'active_products' => Product::active()->count(),
            'total_categories' => Category::count(),
            'active_categories' => Category::active()->count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'total_users' => User::where('role', 'user')->count(),
            'total_revenue' => Order::where('payment_status', 'paid')->sum('total_amount'),
        ];

        // Recent orders
        $recentOrders = Order::with(['user', 'orderItems'])
                            ->orderBy('created_at', 'desc')
                            ->limit(5)
                            ->get();

        // Low stock products
        $lowStockProducts = Product::where('quantity', '<=', 5)
                                  ->where('is_active', true)
                                  ->orderBy('quantity', 'asc')
                                  ->limit(10)
                                  ->get();

        // Monthly sales data for charts (last 6 months)
        $monthlySales = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $sales = Order::whereYear('created_at', $month->year)
                         ->whereMonth('created_at', $month->month)
                         ->where('payment_status', 'paid')
                         ->sum('total_amount');
            
            $monthlySales[] = [
                'month' => $month->format('M Y'),
                'sales' => $sales
            ];
        }

        return view('admin.dashboard', compact('stats', 'recentOrders', 'lowStockProducts', 'monthlySales'));
    }

    /**
     * Product management page.
     */
    public function products(Request $request)
    {
        $query = Product::with('category');

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category') && $request->category != '') {
            $query->where('category_id', $request->category);
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('is_active', $request->status == 'active');
        }

        // Sorting
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'stock':
                $query->orderBy('quantity', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(15);
        $categories = Category::active()->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Category management page.
     */
    public function categories(Request $request)
    {
        $query = Category::withCount('products');

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('is_active', $request->status == 'active');
        }

        $categories = $query->orderBy('name', 'asc')->paginate(15);

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Order management page.
     */
    public function orders(Request $request)
    {
        $query = Order::with(['user', 'orderItems']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('shipping_name', 'like', "%{$search}%")
                  ->orWhere('shipping_phone', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->has('payment_status') && $request->payment_status != '') {
            $query->where('payment_status', $request->payment_status);
        }

        // Date filter
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Show order details.
     */
    public function orderShow(Order $order)
    {
        $order->load(['user', 'orderItems.product']);
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update order status.
     */
    public function updateOrderStatus(Request $request, Order $order)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            ]);

            $oldStatus = $order->status;
            $newStatus = $request->status;

            // Handle specific status changes
            if ($newStatus === 'shipped' && $oldStatus !== 'shipped') {
                $order->markAsShipped();
            } elseif ($newStatus === 'delivered' && $oldStatus !== 'delivered') {
                $order->markAsDelivered();
            } elseif ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                if (!$order->canBeCancelled()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This order cannot be cancelled.'
                    ]);
                }
                $order->cancel();
            } else {
                $order->update(['status' => $newStatus]);
            }

            return response()->json([
                'success' => true,
                'message' => "Order status updated to {$newStatus}."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * User management page.
     */
    public function users(Request $request)
    {
        $query = User::with(['orders' => function($q) {
            $q->orderBy('created_at', 'desc')->limit(1);
        }]);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role') && $request->role != '') {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show user details.
     */
    public function userShow(User $user)
    {
        $user->load(['orders.orderItems.product']);
        
        $userStats = [
            'total_orders' => $user->orders()->count(),
            'total_spent' => $user->orders()->where('payment_status', 'paid')->sum('total_amount'),
            'last_order' => $user->orders()->latest()->first(),
        ];

        return view('admin.users.show', compact('user', 'userStats'));
    }

    /**
     * Update user role.
     */
    public function updateUserRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:admin,user',
        ]);

        // Prevent changing own role to user
        if ($user->id === auth()->id() && $request->role === 'user') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot change your own role to user.'
                ]);
            }
            return redirect()->back()
                            ->with('error', 'You cannot change your own role to user.');
        }

        $user->update(['role' => $request->role]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "User role updated to {$request->role}."
            ]);
        }

        return redirect()->back()
                        ->with('success', "User role updated to {$request->role}.");
    }

    /**
     * Generate reports.
     */
    public function reports(Request $request)
    {
        $period = $request->get('period', '30days');
        
        $startDate = match($period) {
            '7days' => Carbon::now()->subDays(7),
            '30days' => Carbon::now()->subDays(30),
            '3months' => Carbon::now()->subMonths(3),
            '1year' => Carbon::now()->subYear(),
            default => Carbon::now()->subDays(30),
        };

        $reportData = [
            'sales_summary' => [
                'total_sales' => Order::where('created_at', '>=', $startDate)
                                    ->where('payment_status', 'paid')
                                    ->sum('total_amount'),
                'total_orders' => Order::where('created_at', '>=', $startDate)->count(),
                'avg_order_value' => Order::where('created_at', '>=', $startDate)
                                         ->where('payment_status', 'paid')
                                         ->avg('total_amount'),
            ],
            'top_products' => Product::select('products.id', 'products.name', 'products.sku', 'products.image', 'products.price', 'products.sale_price', 'products.category_id')
                                   ->join('order_items', 'products.id', '=', 'order_items.product_id')
                                   ->join('orders', 'order_items.order_id', '=', 'orders.id')
                                   ->where('orders.created_at', '>=', $startDate)
                                   ->where('orders.payment_status', 'paid')
                                   ->groupBy('products.id', 'products.name', 'products.sku', 'products.image', 'products.price', 'products.sale_price', 'products.category_id')
                                   ->selectRaw('SUM(order_items.quantity) as total_sold')
                                   ->with('category')
                                   ->orderBy('total_sold', 'desc')
                                   ->limit(10)
                                   ->get(),
            'recent_customers' => User::where('created_at', '>=', $startDate)
                                    ->where('role', 'user')
                                    ->withCount('orders')
                                    ->orderBy('created_at', 'desc')
                                    ->limit(10)
                                    ->get(),
        ];

        return view('admin.reports', compact('reportData', 'period'));
    }
}
