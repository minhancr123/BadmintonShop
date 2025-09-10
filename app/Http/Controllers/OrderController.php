<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\TryCatch;
use Barryvdh\DomPDF\Facade\Pdf;

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
            'payment_method' => 'required|string|in:cod,bank_transfer,credit_card',
            'sub_payment' => 'required_if:payment_method,credit_card|in:momo,vnpay,atm',
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

            if ($request->payment_method === 'credit_card') {
            if ($request->sub_payment === 'momo') {
                DB::rollback();
                return $this->momoPayment($total, $request);
            }
            if ($request->sub_payment === 'vnpay') {
                DB::rollback();
                return $this->vnpayPayment($total, $request);
            }
            if ($request->sub_payment === 'atm') {
                DB::rollback();
                return $this->atmPayment($total, $request);
            }
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

    private function momoPayment($amount, Request $request)
    {
        $endpoint    = "https://test-payment.momo.vn/v2/gateway/api/create";
        $partnerCode = "MOMOBKUN20180529";
        $accessKey   = "klm05TvNBzhg7h7j";
        $secretKey   = "at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa";

        $orderId     = time() . "";
        $orderInfo   = "Thanh toán đơn hàng #" . $orderId;
        $redirectUrl = route('momo.return');
        $ipnUrl      = route('momo.ipn');
        $requestId   = time() . "";
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

        // Lưu tạm thông tin order vào session để khi MoMo trả về sẽ xử lý
        session()->put('pending_order', [
            'shipping_name' => $request->shipping_name,
            'shipping_phone' => $request->shipping_phone,
            'shipping_address' => $request->shipping_address,
            'notes' => $request->notes,
            'cart' => session()->get('cart', []),
            'amount' => $amount,
        ]);

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

        if ($request->resultCode == 0) {
            // Thanh toán thành công → tạo order trong DB
            return $this->createOrderAfterMomo($pendingOrder, 'paid');
        } else {
            return redirect()->route('cart.index')->with('error', 'Thanh toán MoMo thất bại.');
        }
    }

    public function momoIpn(Request $request)
    {
        // MoMo gọi server → có thể xử lý cập nhật trạng thái ở đây
        return response()->json(['message' => 'IPN OK']);
    }

    private function createOrderAfterMomo($pendingOrder, $paymentStatus)
    {
        DB::beginTransaction();
        try {
            $total = $pendingOrder['amount'];
            $cart = $pendingOrder['cart'];

            $order = Order::create([
                'user_id' => auth()->id(),
                'total_amount' => $total,
                'status' => 'pending',
                'payment_status' => $paymentStatus,
                'payment_method' => 'momo',
                'shipping_name' => $pendingOrder['shipping_name'],
                'shipping_phone' => $pendingOrder['shipping_phone'],
                'shipping_address' => $pendingOrder['shipping_address'],
                'notes' => $pendingOrder['notes'],
            ]);

            foreach ($cart as $id => $item) {
                $product = Product::find($id);
                if ($product) {
                    $product->decrement('quantity', $item['quantity']);
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'price' => $product->current_price,
                        'total' => $product->current_price * $item['quantity']
                    ]);
                }
            }

            DB::commit();
            session()->forget(['cart', 'pending_order']);
            return redirect()->route('orders.show', $order)->with('success', 'Thanh toán MoMo thành công!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('cart.index')->with('error', 'Lỗi tạo đơn sau khi thanh toán: ' . $e->getMessage());
        }
    }

    public function vnpayPayment($amount, Request $request)
{
    date_default_timezone_set('Asia/Ho_Chi_Minh');

    $vnp_TmnCode    = env('VNPAY_TMN_CODE', '1VYBIYQP');
    $vnp_HashSecret = env('VNPAY_HASH_SECRET', 'NOH6MBGNLQL9O9OMMFMZ2AX8NIEP50W1');
    $vnp_Url        = env('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
    $vnp_Returnurl  = route('vnpay.return'); // Đảm bảo đây là HTTPS full URL

    // Tạo TxnRef unique hơn
    $vnp_TxnRef = 'ORDER_' . time() . '_' . substr(md5(uniqid()), 0, 4); // Ví dụ: ORDER_1725970000_abcd
    $vnp_OrderInfo = "Thanh toán đơn hàng #" . $vnp_TxnRef;
    $vnp_OrderType = "billpayment";
    $vnp_Amount    = intval($amount) * 100;
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

    // Lưu session với TxnRef mới
    session()->put('pending_order', [
        'amount'          => $amount,
        'shipping_name'   => $request->shipping_name ?? null,
        'shipping_phone'  => $request->shipping_phone ?? null,
        'shipping_address' => $request->shipping_address ?? null,
        'notes'           => $request->notes ?? null,
        'cart'            => session()->get('cart', []),
        'vnp_TxnRef'      => $vnp_TxnRef
    ]);

    // Log để debug
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

    if ($calculatedHash === $vnp_SecureHash) {
        if ($request->vnp_ResponseCode == '00' && $request->vnp_TxnRef == $pendingOrder['vnp_TxnRef']) {
            // Thanh toán thành công, tạo đơn hàng
            $order = $this->createOrderAfterVnpay($pendingOrder, 'paid');
            return redirect()->route('orders.show', $order)
                            ->with('success', 'Thanh toán VNPay thành công!');
        } else {
            return redirect()->route('cart.index')
                            ->with('error', 'Thanh toán VNPay thất bại hoặc bị hủy. Mã lỗi: ' . $request->vnp_ResponseCode);
        }
    } else {
        return redirect()->route('cart.index')
                        ->with('error', 'Chữ ký VNPay không hợp lệ.');
    }
}
private function createOrderAfterVnpay($pendingOrder, $paymentStatus)
{
    DB::beginTransaction();
    try {
        $total = $pendingOrder['amount'];
        $cart = $pendingOrder['cart'];

        // Tạo đơn hàng
        $order = Order::create([
            'user_id' => auth()->id(),
            'total_amount' => $total,
            'status' => 'pending',
            'payment_status' => $paymentStatus,
            'payment_method' => 'vnpay',
            'shipping_name' => $pendingOrder['shipping_name'],
            'shipping_phone' => $pendingOrder['shipping_phone'],
            'shipping_address' => $pendingOrder['shipping_address'],
            'notes' => $pendingOrder['notes'],
        ]);

        // Tạo chi tiết đơn hàng và cập nhật số lượng sản phẩm
        foreach ($cart as $id => $item) {
            $product = Product::lockForUpdate()->find($id);
            if (!$product || !$product->is_active) {
                throw new \Exception("Sản phẩm {$item['name']} không còn tồn tại.");
            }
            if ($product->quantity < $item['quantity']) {
                throw new \Exception("Sản phẩm {$product->name} không đủ số lượng. Còn lại: {$product->quantity}");
            }

            $product->decrement('quantity', $item['quantity']);
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $product->current_price,
                'total' => $product->current_price * $item['quantity']
            ]);
        }

        DB::commit();
        session()->forget(['cart', 'pending_order']);
        return $order;
    } catch (\Exception $e) {
        DB::rollback();
        throw new \Exception('Lỗi tạo đơn sau khi thanh toán VNPay: ' . $e->getMessage());
    }
}

}