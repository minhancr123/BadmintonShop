<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hóa đơn {{ $invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .company, .user, .order-items { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <div class="header">
        <h2>HÓA ĐƠN BÁN HÀNG</h2>
        <p>Số hóa đơn: {{ $invoice_number }}</p>
        <p>Ngày: {{ $invoice_date }}</p>
    </div>

    <div class="company">
        <h4>Cửa hàng: {{ $company['name'] }}</h4>
        <p>Địa chỉ: {{ $company['address'] }}</p>
        <p>Điện thoại: {{ $company['phone'] }}</p>
        <p>Email: {{ $company['email'] }}</p>
        <p>Mã số thuế: {{ $company['tax_code'] }}</p>
    </div>

    <div class="user">
        <h4>Khách hàng: {{ $user->name }}</h4>
        <p>Email: {{ $user->email }}</p>
    </div>

    <div class="order-items">
        <h4>Chi tiết đơn hàng</h4>
        <table>
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Số lượng</th>
                    <th>Đơn giá</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orderItems as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->price, 0, ',', '.') }} đ</td>
                        <td>{{ number_format($item->quantity * $item->price, 0, ',', '.') }} đ</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <h3>Tổng cộng: {{ number_format($order->total_amount, 0, ',', '.') }} đ</h3>
    </div>
</body>
</html>
