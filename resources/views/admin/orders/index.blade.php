@extends('admin.layout')

@section('title', 'Quản lý đơn hàng')
@section('page-title', 'Quản lý đơn hàng')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Đơn hàng</li>
@endsection

@section('content')
<!-- Filters -->
<div class="card admin-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Tìm kiếm</label>
                <input type="text" class="form-control" name="search" value="{{ request('search') }}" 
                       placeholder="Mã đơn, khách hàng, SĐT...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Trạng thái</label>
                <select class="form-select" name="status">
                    <option value="">Tất cả</option>
                    <option value="pending" @selected(request('status') == 'pending')>Chờ xử lý</option>
                    <option value="processing" @selected(request('status') == 'processing')>Đang xử lý</option>
                    <option value="shipped" @selected(request('status') == 'shipped')>Đã gửi</option>
                    <option value="delivered" @selected(request('status') == 'delivered')>Đã giao</option>
                    <option value="cancelled" @selected(request('status') == 'cancelled')>Đã hủy</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Thanh toán</label>
                <select class="form-select" name="payment_status">
                    <option value="">Tất cả</option>
                    <option value="pending" @selected(request('payment_status') == 'pending')>Chờ thanh toán</option>
                    <option value="paid" @selected(request('payment_status') == 'paid')>Đã thanh toán</option>
                    <option value="failed" @selected(request('payment_status') == 'failed')>Thất bại</option>
                    <option value="refunded" @selected(request('payment_status') == 'refunded')>Đã hoàn tiền</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Từ ngày</label>
                <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Đến ngày</label>
                <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Orders Table -->
<div class="card admin-card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-list"></i> Danh sách đơn hàng ({{ $orders->total() }} đơn hàng)
        </h5>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-refresh"></i> Làm mới
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        @if($orders->count() > 0)
            <div class="table-responsive" style="overflow: visible;">
                <table class="table table-hover mb-0 table-admin">
                    <thead class="table-light">
                        <tr>
                            <th>Mã đơn hàng</th>
                            <th>Khách hàng</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái đơn</th>
                            <th>Thanh toán</th>
                            <th>Ngày đặt</th>
                            <th width="120">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td>
                                    <div>
                                        <a href="{{ route('admin.orders.show', $order) }}" 
                                           class="fw-bold text-decoration-none text-primary">
                                            {{ $order->order_number }}
                                        </a>
                                        <br><small class="text-muted">{{ $order->orderItems->count() }} sản phẩm</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <h6 class="mb-0">{{ $order->user->name ?? $order->shipping_name }}</h6>
                                        <small class="text-muted">
                                            @if($order->user)
                                                {{ $order->user->email }}
                                            @endif
                                        </small>
                                        @if($order->shipping_phone)
                                            <br><small class="text-info">{{ $order->shipping_phone }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-success fs-6">{{ number_format($order->total_amount) }}₫</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @switch($order->status)
                                            @case('pending')
                                                <span class="badge bg-warning me-2">Chờ xử lý</span>
                                                @break
                                            @case('processing')
                                                <span class="badge bg-info me-2">Đang xử lý</span>
                                                @break
                                            @case('shipped')
                                                <span class="badge bg-primary me-2">Đã gửi</span>
                                                @break
                                            @case('delivered')
                                                <span class="badge bg-success me-2">Đã giao</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge bg-danger me-2">Đã hủy</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary me-2">{{ $order->status }}</span>
                                        @endswitch
                                        
                                        @if($order->status !== 'delivered' && $order->status !== 'cancelled')
                                            <div class="dropdown position-static">
                                                <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" 
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                                    @if($order->status === 'pending')
                                                        <li><a class="dropdown-item" href="#" onclick="updateStatus({{ $order->id }}, 'processing')">
                                                            <i class="fas fa-cog text-info"></i> Đang xử lý</a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="updateStatus({{ $order->id }}, 'shipped')">
                                                            <i class="fas fa-shipping-fast text-primary"></i> Đã gửi</a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="updateStatus({{ $order->id }}, 'cancelled')">
                                                            <i class="fas fa-times text-danger"></i> Hủy đơn</a></li>
                                                    @elseif($order->status === 'processing')
                                                        <li><a class="dropdown-item" href="#" onclick="updateStatus({{ $order->id }}, 'shipped')">
                                                            <i class="fas fa-shipping-fast text-primary"></i> Đã gửi</a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="updateStatus({{ $order->id }}, 'cancelled')">
                                                            <i class="fas fa-times text-danger"></i> Hủy đơn</a></li>
                                                    @elseif($order->status === 'shipped')
                                                        <li><a class="dropdown-item" href="#" onclick="updateStatus({{ $order->id }}, 'delivered')">
                                                            <i class="fas fa-check text-success"></i> Đã giao hàng</a></li>
                                                    @endif
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @switch($order->payment_status)
                                        @case('pending')
                                            <span class="badge bg-warning">Chờ thanh toán</span>
                                            @break
                                        @case('paid')
                                            <span class="badge bg-success">Đã thanh toán</span>
                                            @break
                                        @case('failed')
                                            <span class="badge bg-danger">Thất bại</span>
                                            @break
                                        @case('refunded')
                                            <span class="badge bg-info">Đã hoàn tiền</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ $order->payment_status }}</span>
                                    @endswitch
                                    @if($order->payment_method)
                                        <br><small class="text-muted">{{ ucfirst($order->payment_method) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <span class="fw-bold">{{ $order->created_at->format('d/m/Y') }}</span>
                                        <br><small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
                                        <br><small class="text-muted">{{ $order->created_at->diffForHumans() }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.orders.show', $order) }}" 
                                           class="btn btn-outline-primary" title="Chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn btn-outline-info" 
                                                onclick="printInvoice({{ $order->id }})" title="In hóa đơn">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                <h5>Không tìm thấy đơn hàng nào</h5>
                <p class="text-muted">Hãy thử thay đổi điều kiện lọc</p>
            </div>
        @endif
    </div>
    
    @if($orders->hasPages())
        <div class="card-footer bg-white">
            {{ $orders->appends(request()->query())->links() }}
        </div>
    @endif
</div>

@endsection

@push('scripts')
<style>
.table-responsive {
    overflow: visible !important;
}
.dropdown-menu {
    z-index: 1050 !important;
    position: absolute !important;
}
</style>
<script>
    // Update Order Status
    async function updateStatus(orderId, status) {
        const statusNames = {
            'processing': 'Đang xử lý',
            'shipped': 'Đã gửi',
            'delivered': 'Đã giao hàng',
            'cancelled': 'Hủy đơn'
        };
        
        const confirmed = await showConfirm(
            'Cập nhật trạng thái đơn hàng',
            `Bạn có chắc muốn cập nhật trạng thái đơn hàng thành "${statusNames[status]}"?`,
            'Cập nhật',
            'fas fa-edit text-primary',
            'btn-primary'
        );
        
        if (confirmed) {
            fetch(`{{ route('admin.orders.update-status', ':orderId') }}`.replace(':orderId', orderId), {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Thành công', 'Trạng thái đơn hàng đã được cập nhật!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Lỗi', data.message || 'Có lỗi xảy ra khi cập nhật trạng thái', 'error');
                }
            })
            .catch(error => {
                showToast('Lỗi', 'Có lỗi xảy ra khi cập nhật trạng thái', 'error');
            });
        }
    }

    // Print Invoice
    function printInvoice(orderId) {
        window.open(`{{ route('orders.invoice', ':orderId') }}`.replace(':orderId', orderId), '_blank');
    }

    // Auto refresh every 30 seconds for pending orders
    @if(request('status') === 'pending' || !request('status'))
        setInterval(function() {
            if (document.visibilityState === 'visible') {
                location.reload();
            }
        }, 30000);
    @endif
</script>
@endpush
