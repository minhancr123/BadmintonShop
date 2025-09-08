<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display user's orders.
     */
    public function index()
    {
        $orders = auth()->user()->orders()
                               ->with('orderItems.product')
                               ->orderBy('created_at', 'desc')
                               ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        // Ensure user can only see their own orders
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this order.');
        }

        $order->load('orderItems.product');

        return view('orders.show', compact('order'));
    }

    /**
     * Show checkout page.
     */
    public function checkout()
    {
        $cart = session()->get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('cart.index')
                            ->with('error', 'Your cart is empty.');
        }

        // Validate cart before checkout
        $cartController = new CartController();
        $errors = $cartController->validateCart();
        
        if (!empty($errors)) {
            return redirect()->route('cart.index')
                            ->with('error', 'Please fix the following issues: ' . implode(', ', $errors));
        }

        $cartItems = [];
        $total = 0;

        foreach ($cart as $id => $item) {
            $product = Product::find($id);
            if ($product) {
                $cartItems[] = [
                    'id' => $id,
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'price' => $product->current_price,
                    'subtotal' => $product->current_price * $item['quantity']
                ];
                $total += $product->current_price * $item['quantity'];
            }
        }

        $user = auth()->user();

        return view('orders.checkout', compact('cartItems', 'total', 'user'));
    }

    /**
     * Store a new order.
     */
    public function store(Request $request)
    {
        $cart = session()->get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('cart.index')
                            ->with('error', 'Your cart is empty.');
        }

        $request->validate([
            'shipping_name' => 'required|string|max:255',
            'shipping_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string',
            'payment_method' => 'required|string|in:cod,bank_transfer',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        
        try {
            // Calculate total and validate stock
            $total = 0;
            $orderItems = [];

            foreach ($cart as $id => $item) {
                $product = Product::lockForUpdate()->find($id);
                
                if (!$product || !$product->is_active) {
                    throw new \Exception("Product {$item['name']} is no longer available.");
                }

                if ($product->quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}. Available: {$product->quantity}");
                }

                $itemTotal = $product->current_price * $item['quantity'];
                $total += $itemTotal;

                $orderItems[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'price' => $product->current_price,
                    'total' => $itemTotal
                ];

                // Reduce product quantity
                $product->decrement('quantity', $item['quantity']);
            }

            // Create order
            $order = Order::create([
                'user_id' => auth()->id(),
                'total_amount' => $total,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $request->payment_method,
                'shipping_name' => $request->shipping_name,
                'shipping_phone' => $request->shipping_phone,
                'shipping_address' => $request->shipping_address,
                'notes' => $request->notes,
            ]);

            // Create order items
            foreach ($orderItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['total']
                ]);
            }

            DB::commit();

            // Clear cart
            session()->forget('cart');

            return redirect()->route('orders.show', $order)
                            ->with('success', 'Order placed successfully! Order number: ' . $order->order_number);

        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Failed to place order: ' . $e->getMessage());
        }
    }

    /**
     * Cancel an order.
     */
    public function cancel(Order $order)
    {
        // Ensure user can only cancel their own orders
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this order.');
        }

        if (!$order->canBeCancelled()) {
            return redirect()->back()
                            ->with('error', 'This order cannot be cancelled.');
        }

        DB::beginTransaction();
        
        try {
            $order->cancel();
            DB::commit();

            return redirect()->back()
                            ->with('success', 'Order cancelled successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                            ->with('error', 'Failed to cancel order: ' . $e->getMessage());
        }
    }

    /**
     * Reorder - add items from previous order to cart.
     */
    public function reorder(Order $order)
    {
        // Ensure user can only reorder their own orders
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this order.');
        }

        $cart = session()->get('cart', []);
        $addedItems = 0;
        $errors = [];

        foreach ($order->orderItems as $orderItem) {
            $product = $orderItem->product;
            
            if (!$product || !$product->is_active) {
                $errors[] = "Product '" . ($orderItem->product ? $orderItem->product->name : 'Unknown') . "' is no longer available.";
                continue;
            }

            if ($product->quantity < $orderItem->quantity) {
                $errors[] = "Only {$product->quantity} units of '{$product->name}' are available.";
                continue;
            }

            $cart[$product->id] = [
                'name' => $product->name,
                'quantity' => $orderItem->quantity,
                'price' => $product->current_price,
                'image' => $product->image
            ];
            
            $addedItems++;
        }

        if ($addedItems > 0) {
            session()->put('cart', $cart);
            $message = "Added {$addedItems} items to cart.";
            
            if (!empty($errors)) {
                $message .= ' Some items could not be added: ' . implode(', ', $errors);
            }
            
            return redirect()->route('cart.index')
                            ->with('success', $message);
        }

        return redirect()->back()
                        ->with('error', 'No items could be added to cart. ' . implode(' ', $errors));
    }

    /**
     * Download order invoice (if implemented).
     */
    public function downloadInvoice(Order $order)
    {
        // Ensure user can only download their own order invoices
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this order.');
        }

        // TODO: Implement PDF invoice generation
        return redirect()->back()
                        ->with('info', 'Invoice download feature will be available soon.');
    }
}
