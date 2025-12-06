<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    /**
     * @var CartService
     */
    private $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Display the shopping cart.
     */
    public function index()
    {
        if ($message = $this->cartService->revalidateCoupon()) {
            session()->flash('warning', $message);
        }

        $summary = $this->cartService->getSummary();

        return view('cart.index', [
            'cartItems' => $summary['items'],
            'subtotal' => $summary['subtotal'],
            'shipping' => $summary['shipping'],
            'discount' => $summary['discount'],
            'total' => $summary['total'],
            'coupon' => $summary['coupon'],
        ]);
    }

    /**
     * Add product to cart.
     */
    public function add(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $product->quantity,
        ]);

        $quantity = $request->quantity;
        $cart = session()->get('cart', []);

        // If product already exists in cart, update quantity
        if (isset($cart[$product->id])) {
            $newQuantity = $cart[$product->id]['quantity'] + $quantity;
            
            // Check if new quantity exceeds available stock
            if ($newQuantity > $product->quantity) {
                return redirect()->back()
                               ->with('error', 'Cannot add more items. Only ' . $product->quantity . ' items available in stock.');
            }
            
            $cart[$product->id]['quantity'] = $newQuantity;
        } else {
            $cart[$product->id] = [
                'name' => $product->name,
                'quantity' => $quantity,
                'price' => $product->current_price,
                'image' => $product->image
            ];
        }

        session()->put('cart', $cart);

        $redirect = redirect()->back()->with('success', 'Product added to cart successfully!');

        if ($message = $this->cartService->revalidateCoupon()) {
            $redirect->with('warning', $message);
        }

        return $redirect;
    }

    /**
     * Update cart item quantity.
     */
    public function update(Request $request, $id)
    {
        \Log::info('Cart Update Request Started', [
            'product_id' => $id,
            'request_data' => $request->all(),
            'is_ajax' => $request->expectsJson(),
            'content_type' => $request->header('Content-Type'),
            'accept_header' => $request->header('Accept'),
            'csrf_token' => $request->header('X-CSRF-TOKEN'),
            'session_id' => session()->getId()
        ]);
        
        try {
            // Log validation attempt
            \Log::info('Validating request data', ['quantity' => $request->get('quantity')]);
            
            $request->validate([
                'quantity' => 'required|integer|min:1',
            ]);
            
            \Log::info('Validation passed');

            $cart = session()->get('cart', []);
            \Log::info('Current cart contents', ['cart' => $cart]);
            
            $product = Product::findOrFail($id);
            \Log::info('Product found', [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_quantity' => $product->quantity
            ]);

            if (isset($cart[$id])) {
                $quantity = $request->quantity;
                \Log::info('Processing quantity update', [
                    'requested_quantity' => $quantity,
                    'available_stock' => $product->quantity,
                    'current_cart_quantity' => $cart[$id]['quantity'] ?? 'N/A'
                ]);
                
                // Check if quantity exceeds available stock
                if ($quantity > $product->quantity) {
                    \Log::warning('Quantity exceeds available stock', [
                        'requested' => $quantity,
                        'available' => $product->quantity
                    ]);
                    
                    if ($request->expectsJson()) {
                        $response = [
                            'success' => false,
                            'message' => 'Cannot update quantity. Only ' . $product->quantity . ' items available in stock.'
                        ];
                        \Log::info('Returning JSON error response', $response);
                        return response()->json($response);
                    }
                    return redirect()->back()
                                   ->with('error', 'Cannot update quantity. Only ' . $product->quantity . ' items available in stock.');
                }

                $cart[$id]['quantity'] = $quantity;
                session()->put('cart', $cart);
                \Log::info('Cart updated successfully', [
                    'updated_cart' => $cart,
                    'session_saved' => session()->save()
                ]);

                if ($request->expectsJson()) {
                    $couponWarning = $this->cartService->revalidateCoupon();
                    $response = [
                        'success' => true,
                        'message' => 'Cart updated successfully!',
                        'new_quantity' => $quantity,
                        'coupon_warning' => $couponWarning,
                    ];
                    \Log::info('Returning JSON success response', $response);
                    return response()->json($response);
                }

                $redirect = redirect()->route('cart.index')->with('success', 'Cart updated successfully!');

                if ($message = $this->cartService->revalidateCoupon()) {
                    $redirect->with('warning', $message);
                }

                return $redirect;
            }
            
            \Log::warning('Product not found in cart', ['product_id' => $id, 'cart_keys' => array_keys($cart)]);

            if ($request->expectsJson()) {
                $response = [
                    'success' => false,
                    'message' => 'Product not found in cart.'
                ];
                \Log::info('Returning JSON error response (not in cart)', $response);
                return response()->json($response);
            }

            return redirect()->route('cart.index')
                            ->with('error', 'Product not found in cart.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', [
                'errors' => $e->errors(),
                'message' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error: ' . $e->getMessage(),
                    'errors' => $e->errors()
                ], 422);
            }
            return redirect()->route('cart.index')
                            ->with('error', 'Validation error: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Unexpected error in cart update', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'product_id' => $id,
                'request_data' => $request->all()
            ]);
            
            if ($request->expectsJson()) {
                $response = [
                    'success' => false,
                    'message' => 'An error occurred while updating the cart: ' . $e->getMessage()
                ];
                \Log::info('Returning JSON error response (exception)', $response);
                return response()->json($response, 500);
            }
            return redirect()->route('cart.index')
                            ->with('error', 'An error occurred while updating the cart.');
        }
    }

    /**
     * Remove item from cart.
     */
    public function remove(Request $request, $id)
    {
        \Log::info('Cart Remove Request Started', [
            'id' => $id,
            'method' => $request->method(),
            'expects_json' => $request->expectsJson(),
            'session_cart' => session()->get('cart', [])
        ]);

        try {
            $cart = session()->get('cart', []);

            if (isset($cart[$id])) {
                unset($cart[$id]);
                session()->put('cart', $cart);

                \Log::info('Cart Remove Success', [
                    'id' => $id,
                    'remaining_cart' => $cart
                ]);

                if ($request->expectsJson()) {
                    $couponWarning = $this->cartService->revalidateCoupon();

                    return response()->json([
                        'success' => true,
                        'message' => 'Product removed from cart successfully!',
                        'coupon_warning' => $couponWarning,
                    ]);
                }

                $redirect = redirect()->route('cart.index')->with('success', 'Product removed from cart successfully!');

                if ($message = $this->cartService->revalidateCoupon()) {
                    $redirect->with('warning', $message);
                }

                return $redirect;
            }

            \Log::warning('Cart Remove Failed - Product Not Found', [
                'id' => $id,
                'cart_keys' => array_keys($cart)
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found in cart.'
                ]);
            }

            return redirect()->route('cart.index')
                            ->with('error', 'Product not found in cart.');
        } catch (\Exception $e) {
            \Log::error('Cart Remove Exception', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while removing the item: ' . $e->getMessage()
                ]);
            }
            return redirect()->route('cart.index')
                            ->with('error', 'An error occurred while removing the item.');
        }
    }

    /**
     * Clear entire cart.
     */
    public function clear()
    {
        session()->forget('cart');
        $this->cartService->forgetCoupon();

        return redirect()->route('cart.index')
                        ->with('success', 'Cart cleared successfully!');
    }

    /**
     * Get cart count for display in header.
     */
    public function getCartCount()
    {
        $cart = $this->cartService->getCart();
        $count = array_sum(array_column($cart, 'quantity'));
        
        return response()->json(['count' => $count]);
    }

    /**
     * Get cart total amount.
     */
    public function getCartTotal()
    {
        return $this->cartService->calculateSubtotal();
    }

    /**
     * Validate cart items before checkout.
     */
    public function validateCart()
    {
        return $this->cartService->validateCartItems();
    }
}
