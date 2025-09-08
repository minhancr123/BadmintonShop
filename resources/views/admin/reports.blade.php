@extends('admin.layout')

@section('title', 'Báo cáo và thống kê')
@section('page-title', 'Báo cáo và thống kê')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Báo cáo</li>
@endsection

@section('page-actions')
    <div class="btn-group">
        <a href="{{ route('admin.reports') }}?period=7days" 
           class="btn btn-outline-primary btn-admin @if($period === '7days') active @endif">7 ngày</a>
        <a href="{{ route('admin.reports') }}?period=30days" 
           class="btn btn-outline-primary btn-admin @if($period === '30days') active @endif">30 ngày</a>
        <a href="{{ route('admin.reports') }}?period=3months" 
           class="btn btn-outline-primary btn-admin @if($period === '3months') active @endif">3 tháng</a>
        <a href="{{ route('admin.reports') }}?period=1year" 
           class="btn btn-outline-primary btn-admin @if($period === '1year') active @endif">1 năm</a>
    </div>
@endsection

@section('content')
<div class="row">
    <!-- Sales Summary -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card admin-card stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="text-uppercase text-white-50 fw-bold small">Doanh số</div>
                        <div class="h3 mb-0 text-white">{{ number_format($reportData['sales_summary']['total_sales']) }}₫</div>
                        <small class="text-white-50">
                            Kỳ {{ $period === '7days' ? '7 ngày' : ($period === '30days' ? '30 ngày' : ($period === '3months' ? '3 tháng' : '1 năm')) }} qua
                        </small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card admin-card stats-card success">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="text-uppercase text-white-50 fw-bold small">Đơn hàng</div>
                        <div class="h3 mb-0 text-white">{{ number_format($reportData['sales_summary']['total_orders']) }}</div>
                        <small class="text-white-50">Tổng đơn hàng</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-cart fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card admin-card stats-card info">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="text-uppercase fw-bold small">Giá trị TB</div>
                        <div class="h3 mb-0">{{ number_format($reportData['sales_summary']['avg_order_value']) }}₫</div>
                        <small class="text-muted">Trung bình mỗi đơn</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calculator fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Top Products -->
    <div class="col-xl-8 mb-4">
        <div class="card admin-card">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-trophy text-warning"></i> Top sản phẩm bán chạy
                </h5>
            </div>
            <div class="card-body p-0">
                @if($reportData['top_products']->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Sản phẩm</th>
                                    <th>Danh mục</th>
                                    <th>Đã bán</th>
                                    <th>Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reportData['top_products'] as $index => $product)
                                    <tr>
                                        <td>
                                            <div class="rank-badge">
                                                @if($index === 0)
                                                    <i class="fas fa-medal text-warning"></i>
                                                @elseif($index === 1)
                                                    <i class="fas fa-medal text-secondary"></i>
                                                @elseif($index === 2)
                                                    <i class="fas fa-medal text-info"></i>
                                                @else
                                                    {{ $index + 1 }}
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $product->image ? asset('storage/' . $product->image) : 'https://via.placeholder.com/40x40?text=No+Image' }}" 
                                                     class="img-thumbnail me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                                <div>
                                                    <h6 class="mb-0">{{ Str::limit($product->name, 30) }}</h6>
                                                    <small class="text-muted">{{ $product->sku }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $product->category->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-success">{{ $product->total_sold }}</span>
                                            <small class="text-muted">sản phẩm</small>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-primary">{{ number_format($product->total_sold * $product->current_price) }}₫</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-chart-bar fa-3x mb-3"></i>
                        <p>Chưa có dữ liệu bán hàng</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Customers -->
    <div class="col-xl-4 mb-4">
        <div class="card admin-card">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-user-plus text-success"></i> Khách hàng mới
                </h5>
            </div>
            <div class="card-body p-0">
                @if($reportData['recent_customers']->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($reportData['recent_customers'] as $customer)
                            <div class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold">{{ $customer->name }}</div>
                                    <small class="text-muted">{{ $customer->email }}</small>
                                    <br><small class="text-info">{{ $customer->created_at->diffForHumans() }}</small>
                                </div>
                                <span class="badge bg-primary rounded-pill">
                                    {{ $customer->orders_count }} đơn
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <p>Chưa có khách hàng mới</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Additional Charts Row -->
<div class="row">
    <!-- Sales by Category Chart -->
    <div class="col-xl-6 mb-4">
        <div class="card admin-card">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie text-info"></i> Doanh số theo danh mục
                </h5>
            </div>
            <div class="card-body">
                <canvas id="categoryChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Order Status Chart -->
    <div class="col-xl-6 mb-4">
        <div class="card admin-card">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-chart-donut text-success"></i> Trạng thái đơn hàng
                </h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Export Options -->
<div class="row">
    <div class="col-12">
        <div class="card admin-card">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-download text-primary"></i> Xuất báo cáo
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="text-muted mb-3">Tải xuống báo cáo dưới các định dạng khác nhau:</p>
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-outline-success btn-admin" onclick="exportReport('excel')">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                            <button class="btn btn-outline-danger btn-admin" onclick="exportReport('pdf')">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>
                            <button class="btn btn-outline-info btn-admin" onclick="exportReport('csv')">
                                <i class="fas fa-file-csv"></i> CSV
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light p-3 rounded">
                            <h6 class="mb-2">Thống kê tổng quan</h6>
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="fw-bold text-primary">{{ number_format($reportData['sales_summary']['total_sales']) }}₫</div>
                                    <small class="text-muted">Doanh thu</small>
                                </div>
                                <div class="col-4">
                                    <div class="fw-bold text-success">{{ $reportData['sales_summary']['total_orders'] }}</div>
                                    <small class="text-muted">Đơn hàng</small>
                                </div>
                                <div class="col-4">
                                    <div class="fw-bold text-info">{{ $reportData['recent_customers']->count() }}</div>
                                    <small class="text-muted">KH mới</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Category Chart (Sample data - you should replace with real data from controller)
        const ctxCategory = document.getElementById('categoryChart').getContext('2d');
        new Chart(ctxCategory, {
            type: 'pie',
            data: {
                labels: ['Vợt cầu lông', 'Giày', 'Quần áo', 'Phụ kiện', 'Cầu lông'],
                datasets: [{
                    data: [35, 25, 20, 15, 5],
                    backgroundColor: [
                        '#FF6B6B',
                        '#4ECDC4', 
                        '#45B7D1',
                        '#F7DC6F',
                        '#BB8FCE'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Status Chart
        const ctxStatus = document.getElementById('statusChart').getContext('2d');
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: ['Đã giao', 'Đang gửi', 'Đang xử lý', 'Chờ xử lý', 'Đã hủy'],
                datasets: [{
                    data: [60, 20, 10, 7, 3],
                    backgroundColor: [
                        '#28a745',
                        '#007bff',
                        '#17a2b8',
                        '#ffc107',
                        '#dc3545'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    });

    function exportReport(format) {
        const period = '{{ $period }}';
        // TODO: Implement export route
        // window.open(`/admin/reports/export/${format}?period=${period}`, '_blank');
        alert('Export functionality will be implemented soon');
    }
</script>
@endpush
