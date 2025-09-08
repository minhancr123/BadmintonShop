<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Process payment for order
     */
    public function processPayment(Request $request, Order $order)
    {
        $paymentMethod = $request->input('payment_method', 'cod');
        
        switch ($paymentMethod) {
            case 'vnpay':
                return $this->vnpayPayment($order);
            case 'momo':
                return $this->momoPayment($order);
            case 'cod':
            default:
                return $this->codPayment($order);
        }
    }

    /**
     * VNPay Payment
     */
    private function vnpayPayment(Order $order)
    {
        $vnp_TmnCode = config('payment.vnpay.tmn_code');
        $vnp_HashSecret = config('payment.vnpay.hash_secret');
        $vnp_Url = config('payment.vnpay.url');
        $vnp_ReturnUrl = config('payment.vnpay.return_url');

        $vnp_TxnRef = $order->order_number;
        $vnp_OrderInfo = "Thanh toán đơn hàng: " . $order->order_number;
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $order->total_amount * 100; // VNPay uses cents
        $vnp_Locale = 'vn';
        $vnp_BankCode = '';
        $vnp_IpAddr = request()->ip();

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_ReturnUrl,
            "vnp_TxnRef" => $vnp_TxnRef
        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        // Update order payment method
        $order->update(['payment_method' => 'vnpay']);

        return redirect($vnp_Url);
    }

    /**
     * MoMo Payment
     */
    private function momoPayment(Order $order)
    {
        $partnerCode = config('payment.momo.partner_code');
        $accessKey = config('payment.momo.access_key');
        $secretKey = config('payment.momo.secret_key');
        $endpoint = config('payment.momo.endpoint');
        $returnUrl = config('payment.momo.return_url');
        $notifyurl = config('payment.momo.notify_url');

        $orderId = $order->order_number;
        $requestId = $orderId . "_" . time();
        $amount = (string)$order->total_amount;
        $orderInfo = "Thanh toán đơn hàng: " . $order->order_number;
        $requestType = "payWithATM";
        $extraData = "";

        // Create signature
        $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl=" . $notifyurl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $returnUrl . "&requestId=" . $requestId . "&requestType=" . $requestType;
        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        $data = array(
            'partnerCode' => $partnerCode,
            'partnerName' => "Badminton Shop",
            'storeId' => "BadmintonShop",
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $returnUrl,
            'ipnUrl' => $notifyurl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature
        );

        $result = $this->execPostRequest($endpoint, json_encode($data));
        $jsonResult = json_decode($result, true);

        // Update order payment method
        $order->update(['payment_method' => 'momo']);

        if (isset($jsonResult['payUrl'])) {
            return redirect($jsonResult['payUrl']);
        } else {
            return redirect()->route('orders.show', $order)->with('error', 'Lỗi thanh toán MoMo');
        }
    }

    /**
     * Cash on Delivery
     */
    private function codPayment(Order $order)
    {
        $order->update([
            'payment_method' => 'cod',
            'payment_status' => 'pending'
        ]);

        return redirect()->route('orders.show', $order)->with('success', 'Đơn hàng đã được tạo thành công!');
    }

    /**
     * VNPay Return Handler
     */
    public function vnpayReturn(Request $request)
    {
        $vnp_HashSecret = config('payment.vnpay.hash_secret');
        $inputData = $request->all();
        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHash']);

        ksort($inputData);
        $hashData = "";
        foreach ($inputData as $key => $value) {
            $hashData .= urlencode($key) . "=" . urlencode($value) . '&';
        }
        $hashData = rtrim($hashData, '&');

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        $order = Order::where('order_number', $inputData['vnp_TxnRef'])->first();

        if ($secureHash == $vnp_SecureHash) {
            if ($inputData['vnp_ResponseCode'] == '00') {
                // Payment success
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'confirmed'
                ]);
                return redirect()->route('orders.show', $order)->with('success', 'Thanh toán thành công!');
            } else {
                // Payment failed
                $order->update(['payment_status' => 'failed']);
                return redirect()->route('orders.show', $order)->with('error', 'Thanh toán thất bại!');
            }
        } else {
            return redirect()->route('orders.show', $order)->with('error', 'Chữ ký không hợp lệ!');
        }
    }

    /**
     * MoMo Return Handler
     */
    public function momoReturn(Request $request)
    {
        $orderId = $request->input('orderId');
        $resultCode = $request->input('resultCode');

        $order = Order::where('order_number', $orderId)->first();

        if ($resultCode == 0) {
            // Payment success
            $order->update([
                'payment_status' => 'paid',
                'status' => 'confirmed'
            ]);
            return redirect()->route('orders.show', $order)->with('success', 'Thanh toán thành công!');
        } else {
            // Payment failed
            $order->update(['payment_status' => 'failed']);
            return redirect()->route('orders.show', $order)->with('error', 'Thanh toán thất bại!');
        }
    }

    /**
     * Execute POST request
     */
    private function execPostRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data))
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
