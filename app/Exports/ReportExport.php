<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class ReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $startDate;
    protected $type;

    public function __construct($startDate, $type)
    {
        $this->startDate = $startDate;
        $this->type = $type;
    }

    public function collection()
    {
        $query = Order::where('created_at', '>=', $this->startDate)
            ->where('payment_status', 'paid')
            ->with(['user', 'orderItems.product']);

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Mã đơn hàng',
            'Khách hàng',
            'Tổng tiền',
            'Trạng thái',
            'Ngày đặt hàng',
            'Sản phẩm',
            'Số lượng',
            'Giá',
        ];
    }

    public function map($order): array
    {
        $products = $order->orderItems->map(function ($item) {
            return "{$item->product->name} (x{$item->quantity})";
        })->implode(', ');

        return [
            $order->order_number,
            $order->user->name,
            number_format($order->total_amount, 2),
            $order->status,
            $order->created_at->format('d/m/Y H:i'),
            $products,
            $order->orderItems->sum('quantity'),
            number_format($order->orderItems->sum('total'), 2),
        ];
    }
}