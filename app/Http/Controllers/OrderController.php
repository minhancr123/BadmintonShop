<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    private CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Display user's orders.
     */
    public function index()
    {
        $userId = auth()->id();

        if (!$userId) {
            abort(403);
        }

        $orders = Order::query()
                        ->with('orderItems.product')
                        ->where('user_id', $userId)
                        ->latest()
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
        $cart = $this->cartService->getCart();

        if (empty($cart)) {
            return redirect()->route('cart.index')
                            ->with('error', 'Your cart is empty.');
        }

        if ($message = $this->cartService->revalidateCoupon()) {
            session()->flash('warning', $message);
        }

        $errors = $this->cartService->validateCartItems($cart);

        if (!empty($errors)) {
            return redirect()->route('cart.index')
                            ->with('error', 'Please fix the following issues: ' . implode(', ', $errors));
        }

        $summary = $this->cartService->getSummary();

        return view('orders.checkout', [
            'cartItems' => $summary['items'],
            'subtotal' => $summary['subtotal'],
            'shipping' => $summary['shipping'],
            'discount' => $summary['discount'],
            'total' => $summary['total'],
            'coupon' => $summary['coupon'],
            'user' => auth()->user(),
        ]);
    }

    /**
     * Store a new order.
     */
    public function store(Request $request)
    {
        $cart = $this->cartService->getCart();

        if (empty($cart)) {
            return redirect()->route('cart.index')
                            ->with('error', 'Your cart is empty.');
        }

        $request->validate([
            'shipping_name' => 'required|string|max:255',
            'shipping_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string',
            'payment_method' => 'required|string|in:cod,bank_transfer,credit_card',
            'sub_payment' => 'required_if:payment_method,credit_card|in:momo,vnpay,atm',
            'notes' => 'nullable|string|max:500',
        ]);

        $errors = $this->cartService->validateCartItems();

        if (!empty($errors)) {
            return redirect()->route('cart.index')
                            ->with('error', 'Please fix the following issues: ' . implode(', ', $errors));
        }

        if ($message = $this->cartService->revalidateCoupon()) {
            session()->flash('warning', $message);
        }

        $summary = $this->cartService->getSummary();

        if (empty($summary['items'])) {
            return redirect()->route('cart.index')
                            ->with('error', 'Your cart is empty.');
        }

        $coupon = $this->cartService->getSessionCoupon();
        $subtotal = $summary['subtotal'];

        if ($coupon) {
            $error = $this->cartService->validateCoupon($coupon, $subtotal);

            if ($error) {
                $this->cartService->forgetCoupon();

                return redirect()->route('cart.index')
                                ->with('error', $error);
            }
        }

        $shippingData = [
            'shipping_name' => $request->shipping_name,
            'shipping_phone' => $request->shipping_phone,
            'shipping_address' => $request->shipping_address,
            'notes' => $request->notes,
        ];

        if (round($summary['total']) <= 0) {
            try {
                $order = $this->persistOrder(
                    $cart,
                    $shippingData,
                    $coupon ? 'coupon' : $request->payment_method,
                    'paid',
                    $coupon
                );

                session()->forget(['cart', 'pending_order']);
                $this->cartService->forgetCoupon();

                return redirect()->route('orders.show', $order)
                                ->with('success', 'Order placed successfully! Order number: ' . $order->order_number);
            } catch (\Exception $e) {
                return redirect()->back()
                                ->withInput()
                                ->with('error', 'Failed to place order: ' . $e->getMessage());
            }
        }

        if ($request->payment_method === 'credit_card') {
            if (!$request->filled('sub_payment')) {
                return redirect()->back()
                                ->withInput()
                                ->with('error', 'Vui lòng chọn cổng thanh toán.');
            }

            $pendingOrder = array_merge($shippingData, [
                'cart' => $cart,
                'coupon_id' => $coupon ? $coupon->id : null,
                'subtotal_amount' => $summary['subtotal'],
                'shipping_amount' => $summary['shipping'],
                'discount_amount' => $summary['discount'],
                'total_amount' => $summary['total'],
            ]);

            if ($request->sub_payment === 'momo') {
                return $this->momoPayment($pendingOrder, $request);
            }

            if ($request->sub_payment === 'vnpay') {
                return $this->vnpayPayment($pendingOrder, $request);
            }

            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Phương thức thanh toán không được hỗ trợ.');
        }

        try {
            $order = $this->persistOrder(
                $cart,
                $shippingData,
                $request->payment_method,
                'pending',
                $coupon
            );

            session()->forget(['cart', 'pending_order']);
            $this->cartService->forgetCoupon();

            return redirect()->route('orders.show', $order)
                            ->with('success', 'Order placed successfully! Order number: ' . $order->order_number);
        } catch (\Exception $e) {
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

        //Allow download only for delivered orders
        if($order->status !== "delivered") {
            return response()->json([
                'success' => false,
                'error' => 'Invoice can only be downloaded for delivered orders.'
            ], 403);
        }

        //Load relationship data
        $order->load(([
            'orderItems.product',
            'user'
        ]));

        $invoiceData = [
            'order' => $order,
            'orderItems' => $order->orderItems,
            'user' => $order->user,
            'company' => [
                'name' => 'Badminton Shop',
                'address' => '123 Đường ABC, Quận 1, TP.HCM',
                'phone' => '0123 456 789',
                'email' => 'info@badmintonshop.com',
                'tax_code' => '0123456789'
            ],
            'invoice_number' => 'HĐ-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
             'invoice_date' => now()->format('d/m/Y'),
            'due_date' => now()->addDays(30)->format('d/m/Y')
        ];
        
        try{
            $pdf = PDF::loadView("orders.invoice", $invoiceData);
            $pdf->setPaper('A4', 'portrait');
            $filename = $invoiceData['invoice_number'] . '.pdf';

            
            return $pdf->download($filename);
        } catch (\Exception $e) {
            return redirect()->back()
                            ->with('error', 'Failed to generate invoice: ' . $e->getMessage());
        }

       
    }

    private function momoPayment(array $pendingOrder, Request $request)
    {
        $amount = (int) round($pendingOrder['total_amount'] ?? 0);

        if ($amount <= 0) {
            return redirect()->route('cart.index')
                            ->with('error', 'Số tiền thanh toán không hợp lệ.');
        }

        $endpoint    = "https://test-payment.momo.vn/v2/gateway/api/create";
        $partnerCode = "MOMOBKUN20180529";
        $accessKey   = "klm05TvNBzhg7h7j";
        $secretKey   = "at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa";

        $orderId     = (string) time();
        $orderInfo   = "Thanh toán đơn hàng #" . $orderId;
        $redirectUrl = route('momo.return');
        $ipnUrl      = route('momo.ipn');
        $requestId   = (string) time();
        $requestType = "payWithATM";
        $extraData   = "";

        $rawHash = "accessKey=" . $accessKey .
                "&amount=" . $amount .
                "&extraData=" . $extraData .
                "&ipnUrl=" . $ipnUrl .
                "&orderId=" . $orderId .
                "&orderInfo=" . $orderInfo .
                "&partnerCode=" . $partnerCode .
                "&redirectUrl=" . $redirectUrl .
                "&requestId=" . $requestId .
                "&requestType=" . $requestType;

        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        $data = [
            'partnerCode' => $partnerCode,
            'partnerName' => "Test",
            'storeId'     => "MomoTestStore",
            'requestId'   => $requestId,
            'amount'      => $amount,
            'orderId'     => $orderId,
            'orderInfo'   => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl'      => $ipnUrl,
            'lang'        => 'vi',
            'extraData'   => $extraData,
            'requestType' => $requestType,
            'signature'   => $signature
        ];

        $result = $this->execPostRequest($endpoint, json_encode($data));
        $jsonResult = json_decode($result, true);

        if (!is_array($jsonResult) || empty($jsonResult['payUrl'])) {
            return redirect()->route('cart.index')
                            ->with('error', 'Không thể khởi tạo thanh toán MoMo. Vui lòng thử lại.');
        }

        session()->put('pending_order', array_merge($pendingOrder, [
            'amount' => $amount,
            'payment_gateway' => 'momo',
            'order_id' => $orderId,
            'request_id' => $requestId,
        ]));

        return redirect()->to($jsonResult['payUrl']);
    }

    private function execPostRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public function momoReturn(Request $request)
    {
        $pendingOrder = session()->get('pending_order');

        if (!$pendingOrder) {
            return redirect()->route('cart.index')->with('error', 'Không tìm thấy đơn hàng tạm.');
        }

        if ((int) $request->resultCode === 0) {
            try {
                $order = $this->createOrderAfterMomo($pendingOrder, 'paid');

                return redirect()->route('orders.show', $order)
                                ->with('success', 'Thanh toán MoMo thành công!');
            } catch (\Exception $e) {
                return redirect()->route('cart.index')
                                ->with('error', 'Lỗi tạo đơn sau khi thanh toán: ' . $e->getMessage());
            }
        }

        return redirect()->route('cart.index')->with('error', 'Thanh toán MoMo thất bại.');
    }

    public function momoIpn(Request $request)
    {
        // MoMo gọi server → có thể xử lý cập nhật trạng thái ở đây
        return response()->json(['message' => 'IPN OK']);
    }

    private function createOrderAfterMomo(array $pendingOrder, string $paymentStatus): Order
    {
        return $this->finalizePendingOrder($pendingOrder, 'momo', $paymentStatus);
    }

    private function vnpayPayment(array $pendingOrder, Request $request)
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh');

        $amount = (int) round($pendingOrder['total_amount'] ?? 0);

        if ($amount <= 0) {
            return redirect()->route('cart.index')
                            ->with('error', 'Số tiền thanh toán không hợp lệ.');
        }

        $vnp_TmnCode    = env('VNPAY_TMN_CODE', '1VYBIYQP');
        $vnp_HashSecret = env('VNPAY_HASH_SECRET', 'NOH6MBGNLQL9O9OMMFMZ2AX8NIEP50W1');
        $vnp_Url        = env('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
        $vnp_Returnurl  = route('vnpay.return');

        $vnp_TxnRef = 'ORDER_' . time() . '_' . substr(md5(uniqid()), 0, 4);
        $vnp_OrderInfo = "Thanh toán đơn hàng #" . $vnp_TxnRef;
        $vnp_OrderType = "billpayment";
        $vnp_Amount    = $amount * 100;
        $vnp_Locale    = 'vn';
        $vnp_IpAddr    = $request->ip();

        $inputData = [
            "vnp_Version"    => "2.1.0",
            "vnp_TmnCode"    => $vnp_TmnCode,
            "vnp_Amount"     => $vnp_Amount,
            "vnp_Command"    => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode"   => "VND",
            "vnp_IpAddr"     => $vnp_IpAddr,
            "vnp_Locale"     => $vnp_Locale,
            "vnp_OrderInfo"  => $vnp_OrderInfo,
            "vnp_OrderType"  => $vnp_OrderType,
            "vnp_ReturnUrl"  => $vnp_Returnurl,
            "vnp_TxnRef"     => $vnp_TxnRef
        ];

        ksort($inputData);
        $hashdata = http_build_query($inputData, '', '&');
        $query    = http_build_query($inputData, '', '&');
        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);

        $vnp_Url = $vnp_Url . "?" . $query . "&vnp_SecureHash=" . $vnpSecureHash;

        session()->put('pending_order', array_merge($pendingOrder, [
            'amount' => $amount,
            'vnp_TxnRef' => $vnp_TxnRef,
            'payment_gateway' => 'vnpay',
        ]));

        \Log::info('VNPay Full URL: ' . $vnp_Url);
        \Log::info('VNPay Return URL: ' . $vnp_Returnurl);
        \Log::info('VNPay TxnRef: ' . $vnp_TxnRef);

        return redirect()->away($vnp_Url);
    }

    public function vnpayReturn(Request $request)
{
    $vnp_HashSecret = env('VNPAY_HASH_SECRET', 'NOH6MBGNLQL9O9OMMFMZ2AX8NIEP50W1');
    $pendingOrder = session()->get('pending_order');

    if (!$pendingOrder) {
        return redirect()->route('cart.index')->with('error', 'Không tìm thấy đơn hàng tạm.');
    }

    // Kiểm tra chữ ký trả về
    $vnp_SecureHash = $request->vnp_SecureHash;
    $inputData = $request->all();

    // Loại bỏ vnp_SecureHash và vnp_SecureHashType khỏi tính toán hash
    unset($inputData['vnp_SecureHash']);
    unset($inputData['vnp_SecureHashType']);

    // Sắp xếp và tạo hashdata
    ksort($inputData);
    $hashdata = http_build_query($inputData, '', '&');
    $calculatedHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);

    // Ghi log để gỡ lỗi
    \Log::info('VNPay Return Hash Data: ' . $hashdata);
    \Log::info('VNPay Return Secure Hash: ' . $vnp_SecureHash);
    \Log::info('Calculated Hash: ' . $calculatedHash);

    if ($calculatedHash !== $vnp_SecureHash) {
        return redirect()->route('cart.index')
                        ->with('error', 'Chữ ký VNPay không hợp lệ.');
    }

    if ($request->vnp_ResponseCode == '00' && $request->vnp_TxnRef == ($pendingOrder['vnp_TxnRef'] ?? null)) {
        try {
            $order = $this->createOrderAfterVnpay($pendingOrder, 'paid');

            return redirect()->route('orders.show', $order)
                            ->with('success', 'Thanh toán VNPay thành công!');
        } catch (\Exception $e) {
            return redirect()->route('cart.index')
                            ->with('error', 'Lỗi tạo đơn sau khi thanh toán VNPay: ' . $e->getMessage());
        }
    }

    return redirect()->route('cart.index')
                    ->with('error', 'Thanh toán VNPay thất bại hoặc bị hủy. Mã lỗi: ' . $request->vnp_ResponseCode);
}
    private function createOrderAfterVnpay(array $pendingOrder, string $paymentStatus): Order
    {
        return $this->finalizePendingOrder($pendingOrder, 'vnpay', $paymentStatus);
    }

    private function finalizePendingOrder(array $pendingOrder, string $paymentMethod, string $paymentStatus): Order
    {
        $cart = $pendingOrder['cart'] ?? [];

        if (empty($cart)) {
            throw new \Exception('Giỏ hàng trống.');
        }

        $shippingData = [
            'shipping_name' => $pendingOrder['shipping_name'] ?? null,
            'shipping_phone' => $pendingOrder['shipping_phone'] ?? null,
            'shipping_address' => $pendingOrder['shipping_address'] ?? null,
            'notes' => $pendingOrder['notes'] ?? null,
        ];

        foreach (['shipping_name', 'shipping_phone', 'shipping_address'] as $field) {
            if (empty($shippingData[$field])) {
                throw new \Exception('Thiếu thông tin giao hàng.');
            }
        }

        $coupon = null;

        if (!empty($pendingOrder['coupon_id'])) {
            $coupon = Coupon::find($pendingOrder['coupon_id']);
        }

        $order = $this->persistOrder($cart, $shippingData, $paymentMethod, $paymentStatus, $coupon);

        session()->forget(['cart', 'pending_order']);
        $this->cartService->forgetCoupon();

        return $order;
    }

    private function persistOrder(array $cart, array $shippingData, string $paymentMethod, string $paymentStatus, ?Coupon $coupon = null): Order
    {
        $userId = auth()->id();

        if (!$userId) {
            throw new \Exception('Bạn cần đăng nhập để tiếp tục.');
        }

        return DB::transaction(function () use ($cart, $shippingData, $paymentMethod, $paymentStatus, $coupon, $userId) {
            if (empty($cart)) {
                throw new \Exception('Giỏ hàng của bạn đang trống.');
            }

            $subtotal = 0;
            $orderItemsData = [];

            foreach ($cart as $id => $item) {
                $product = Product::lockForUpdate()->find($id);

                if (!$product || !$product->is_active) {
                    throw new \Exception("Sản phẩm '{$item['name']}' hiện không khả dụng.");
                }

                $quantity = (int) ($item['quantity'] ?? 0);

                if ($quantity <= 0) {
                    throw new \Exception("Số lượng không hợp lệ cho sản phẩm '{$product->name}'.");
                }

                if ($product->quantity < $quantity) {
                    throw new \Exception("Sản phẩm '{$product->name}' chỉ còn {$product->quantity} sản phẩm.");
                }

                $price = $product->current_price;
                $lineTotal = $price * $quantity;

                $subtotal += $lineTotal;

                $orderItemsData[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => $lineTotal,
                ];
            }

            $shippingAmount = $this->cartService->calculateShipping($subtotal);
            $discountAmount = 0;
            $couponCode = null;

            if ($coupon) {
                $error = $this->cartService->validateCoupon($coupon, $subtotal);

                if ($error) {
                    throw new \Exception($error);
                }

                $discountAmount = $this->cartService->calculateDiscount($coupon, $subtotal);

                if ($discountAmount > 0) {
                    $couponCode = $coupon->code;
                } else {
                    $coupon = null;
                }
            }

            $totalAmount = max(0, $subtotal + $shippingAmount - $discountAmount);

            $order = Order::create([
                'user_id' => $userId,
                'subtotal_amount' => $subtotal,
                'discount_amount' => $discountAmount,
                'shipping_amount' => $shippingAmount,
                'total_amount' => $totalAmount,
                'coupon_id' => $coupon ? $coupon->id : null,
                'coupon_code' => $couponCode,
                'status' => 'pending',
                'payment_status' => $paymentStatus,
                'payment_method' => $paymentMethod,
                'shipping_name' => $shippingData['shipping_name'],
                'shipping_phone' => $shippingData['shipping_phone'],
                'shipping_address' => $shippingData['shipping_address'],
                'notes' => $shippingData['notes'] ?? null,
            ]);

            foreach ($orderItemsData as $itemData) {
                $itemData['product']->decrement('quantity', $itemData['quantity']);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $itemData['product']->id,
                    'quantity' => $itemData['quantity'],
                    'price' => $itemData['price'],
                    'total' => $itemData['total'],
                ]);
            }

            if ($coupon && $discountAmount > 0) {
                CouponUsage::create([
                    'coupon_id' => $coupon->id,
                    'order_id' => $order->id,
                    'user_id' => $userId,
                    'discount_amount' => $discountAmount,
                ]);
            }

            return $order;
        });
    }

}