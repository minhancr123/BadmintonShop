@extends('admin.layout')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card admin-card stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="text-uppercase text-white-50 fw-bold small">Sản phẩm</div>
                        <div class="h2 mb-0 text-white">{{ number_format($stats['total_products']) }}</div>
                        <small class="text-white-50">{{ $stats['active_products'] }} đang hoạt động</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-box fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card admin-card stats-card success">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="text-uppercase text-white-50 fw-bold small">Đơn hàng</div>
                        <div class="h2 mb-0 text-white">{{ number_format($stats['total_orders']) }}</div>
                        <small class="text-white-50">{{ $stats['pending_orders'] }} chờ xử lý</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-cart fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card admin-card stats-card warning">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="text-uppercase text-white-50 fw-bold small">Khách hàng</div>
                        <div class="h2 mb-0 text-white">{{ number_format($stats['total_users']) }}</div>
                        <small class="text-white-50">Người dùng đã đăng ký</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card admin-card stats-card info">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="text-uppercase fw-bold small">Doanh thu</div>
                        <div class="h2 mb-0">{{ number_format($stats['total_revenue']) }}₫</div>
                        <small class="text-muted">Tổng doanh thu</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Sales Chart -->
    <div class="col-xl-8 mb-4">
        <div class="card admin-card">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-chart-area text-primary"></i> Biểu đồ doanh số (6 tháng gần đây)
                </h5>
            </div>
            <div class="card-body">
                <canvas id="salesChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-xl-4 mb-4">
        <div class="card admin-card">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-rocket text-success"></i> Thao tác nhanh
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-admin">
                        <i class="fas fa-plus"></i> Thêm sản phẩm mới
                    </a>
                    <a href="{{ route('admin.categories.create') }}" class="btn btn-info btn-admin">
                        <i class="fas fa-tag"></i> Thêm danh mục mới
                    </a>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-warning btn-admin">
                        <i class="fas fa-eye"></i> Xem đơn hàng chờ
                    </a>
                    <a href="{{ route('admin.reports') }}" class="btn btn-secondary btn-admin">
                        <i class="fas fa-chart-bar"></i> Xem báo cáo
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Orders -->
    <div class="col-xl-7 mb-4">
        <div class="card admin-card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-clipboard-list text-info"></i> Đơn hàng gần đây
                </h5>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
            </div>
            <div class="card-body p-0">
                @if($recentOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Khách hàng</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Thời gian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.orders.show', $order) }}" class="text-decoration-none fw-bold">
                                                {{ $order->order_number }}
                                            </a>
                                        </td>
                                        <td>{{ $order->user->name ?? $order->shipping_name }}</td>
                                        <td class="fw-bold text-success">{{ number_format($order->total_amount) }}₫</td>
                                        <td>
                                            @switch($order->status)
                                                @case('pending')
                                                    <span class="badge bg-warning">Chờ xử lý</span>
                                                    @break
                                                @case('processing')
                                                    <span class="badge bg-info">Đang xử lý</span>
                                                    @break
                                                @case('shipped')
                                                    <span class="badge bg-primary">Đã gửi</span>
                                                    @break
                                                @case('delivered')
                                                    <span class="badge bg-success">Đã giao</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge bg-danger">Đã hủy</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ $order->status }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $order->created_at->diffForHumans() }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <p>Chưa có đơn hàng nào</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Low Stock Products -->
    <div class="col-xl-5 mb-4">
        <div class="card admin-card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle text-warning"></i> Sản phẩm sắp hết hàng
                </h5>
                <a href="{{ route('admin.products.index') }}?sort=stock" class="btn btn-sm btn-outline-warning">Xem tất cả</a>
            </div>
            <div class="card-body p-0">
                @if($lowStockProducts->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($lowStockProducts as $product)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">{{ Str::limit($product->name, 30) }}</h6>
                                    <small class="text-muted">{{ $product->sku }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-danger rounded-pill">
                                        {{ $product->quantity }} còn lại
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                        <p>Tất cả sản phẩm đều có đủ hàng</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Sales Chart
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('salesChart').getContext('2d');
        const monthlySales = @json($monthlySales);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: monthlySales.map(item => item.month),
                datasets: [{
                    label: 'Doanh số (₫)',
                    data: monthlySales.map(item => item.sales),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('vi-VN').format(value) + '₫';
                            }
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                elements: {
                    point: {
                        hoverRadius: 8
                    }
                }
            }
        });
    });
</script>
@endpush
