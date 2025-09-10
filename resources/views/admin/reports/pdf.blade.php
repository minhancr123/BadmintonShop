<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Báo cáo</title>
    <style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 12px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }

    h1,
    h2 {
        text-align: center;
    }

    .summary {
        margin-bottom: 20px;
    }
    </style>
</head>

<body>
    <h1>Báo cáo doanh số</h1>
    <p>Kỳ:
        {{ $period === '7days' ? '7 ngày' : ($period === '30days' ? '30 ngày' : ($period === '3months' ? '3 tháng' : '1 năm')) }}
    </p>

    <div class="summary">
        <h2>Tổng quan</h2>
        <p>Tổng doanh thu: {{ number_format($sales_summary['total_sales']) }}₫</p>
        <p>Tổng đơn hàng: {{ $sales_summary['total_orders'] }}</p>
        <p>Giá trị trung bình mỗi đơn: {{ number_format($sales_summary['avg_order_value']) }}₫</p>
    </div>

    <h2>Chi tiết đơn hàng</h2>
    <table>
        <thead>
            <tr>
                <th>Mã đơn hàng</th>
                <th>Khách hàng</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Ngày đặt</th>
                <th>Sản phẩm</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td>{{ $order->order_number }}</td>
                <td>{{ $order->user->name }}</td>
                <td>{{ number_format($order->total_amount) }}₫</td>
                <td>{{ $order->status }}</td>
                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                <td>
                    @foreach($order->orderItems as $item)
                    {{ $item->product->name }} (x{{ $item->quantity }})<br>
                    @endforeach
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>