@extends('admin.layout')

@section('title', 'Chi tiết đơn hàng #' . $order->order_number)
@section('page-title', 'Chi tiết đơn hàng')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Đơn hàng</a></li>
    <li class="breadcrumb-item active">#{{ $order->order_number }}</li>
@endsection

@section('page-actions')
    <div class="d-flex gap-2">
        <button class="btn btn-outline-info btn-admin" onclick="printInvoice()">
            <i class="fas fa-print"></i> In hóa đơn
        </button>
        @if($order->status !== 'delivered' && $order->status !== 'cancelled')
            <div class="dropdown">
                <button class="btn btn-primary btn-admin dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-edit"></i> Cập nhật trạng thái
                </button>
                <ul class="dropdown-menu">
                    @if($order->status === 'pending')
                        <li><a class="dropdown-item" href="#" onclick="updateStatus('processing')">
                            <i class="fas fa-cog text-info"></i> Đang xử lý</a></li>
                        <li><a class="dropdown-item" href="#" onclick="updateStatus('shipped')">
                            <i class="fas fa-shipping-fast text-primary"></i> Đã gửi hàng</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" onclick="updateStatus('cancelled')">
                            <i class="fas fa-times text-danger"></i> Hủy đơn hàng</a></li>
                    @elseif($order->status === 'processing')
                        <li><a class="dropdown-item" href="#" onclick="updateStatus('shipped')">
                            <i class="fas fa-shipping-fast text-primary"></i> Đã gửi hàng</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" onclick="updateStatus('cancelled')">
                            <i class="fas fa-times text-danger"></i> Hủy đơn hàng</a></li>
                    @elseif($order->status === 'shipped')
                        <li><a class="dropdown-item" href="#" onclick="updateStatus('delivered')">
                            <i class="fas fa-check text-success"></i> Đã giao hàng</a></li>
                    @endif
                </ul>
            </div>
        @endif
    </div>
@endsection

@section('content')
<div class="row">
    <!-- Order Status & Timeline -->
    <div class="col-12 mb-4">
        <div class="card admin-card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="text-primary">{{ $order->order_number }}</h4>
                        <p class="text-muted mb-2">Đặt hàng: {{ $order->created_at->format('d/m/Y H:i') }}</p>
                        
                        <!-- Status Timeline -->
                        <div class="status-timeline mt-3">
                            <div class="timeline-item {{ $order->status === 'pending' ? 'active' : ($order->created_at ? 'completed' : '') }}">
                                <div class="timeline-marker">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6>Đơn hàng được tạo</h6>
                                    <small>{{ $order->created_at->format('d/m/Y H:i') }}</small>
                                </div>
                            </div>
                            
                            <div class="timeline-item {{ $order->status === 'processing' ? 'active' : ($order->processing_at ? 'completed' : '') }}">
                                <div class="timeline-marker">
                                    <i class="fas fa-cog"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6>Đang xử lý</h6>
                                    @if($order->processing_at)
                                        <small>{{ $order->processing_at->format('d/m/Y H:i') }}</small>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="timeline-item {{ $order->status === 'shipped' ? 'active' : ($order->shipped_at ? 'completed' : '') }}">
                                <div class="timeline-marker">
                                    <i class="fas fa-shipping-fast"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6>Đã gửi hàng</h6>
                                    @if($order->shipped_at)
                                        <small>{{ $order->shipped_at->format('d/m/Y H:i') }}</small>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="timeline-item {{ $order->status === 'delivered' ? 'active completed' : '' }}">
                                <div class="timeline-marker">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6>Đã giao hàng</h6>
                                    @if($order->delivered_at)
                                        <small>{{ $order->delivered_at->format('d/m/Y H:i') }}</small>
                                    @endif
                                </div>
                            </div>
                            
                            @if($order->status === 'cancelled')
                                <div class="timeline-item cancelled">
                                    <div class="timeline-marker">
                                        <i class="fas fa-times"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h6>Đã hủy</h6>
                                        @if($order->cancelled_at)
                                            <small>{{ $order->cancelled_at->format('d/m/Y H:i') }}</small>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="col-md-6 text-md-end">
                        <div class="mb-3">
                            @switch($order->status)
                                @case('pending')
                                    <span class="badge bg-warning fs-6 px-3 py-2">Chờ xử lý</span>
                                    @break
                                @case('processing')
                                    <span class="badge bg-info fs-6 px-3 py-2">Đang xử lý</span>
                                    @break
                                @case('shipped')
                                    <span class="badge bg-primary fs-6 px-3 py-2">Đã gửi hàng</span>
                                    @break
                                @case('delivered')
                                    <span class="badge bg-success fs-6 px-3 py-2">Đã giao hàng</span>
                                    @break
                                @case('cancelled')
                                    <span class="badge bg-danger fs-6 px-3 py-2">Đã hủy</span>
                                    @break
                            @endswitch
                        </div>
                        
                        <div class="mb-3">
                            @switch($order->payment_status)
                                @case('pending')
                                    <span class="badge bg-warning">Chờ thanh toán</span>
                                    @break
                                @case('paid')
                                    <span class="badge bg-success">Đã thanh toán</span>
                                    @break
                                @case('failed')
                                    <span class="badge bg-danger">Thanh toán thất bại</span>
                                    @break
                                @case('refunded')
                                    <span class="badge bg-info">Đã hoàn tiền</span>
                                    @break
                            @endswitch
                        </div>
                        
                        <h3 class="text-success mb-0">{{ number_format($order->total_amount) }}₫</h3>
                        <small class="text-muted">
                            {{ $order->payment_method ? ucfirst($order->payment_method) : 'COD' }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Customer & Shipping Info -->
    <div class="col-lg-6 mb-4">
        <div class="card admin-card h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-user text-primary"></i> Thông tin khách hàng</h5>
            </div>
            <div class="card-body">
                @if($order->user)
                    <div class="mb-3">
                        <strong>Tài khoản:</strong>
                        <p class="mb-1">{{ $order->user->name }}</p>
                        <small class="text-muted">{{ $order->user->email }}</small>
                        @if($order->user->phone)
                            <br><small class="text-info">{{ $order->user->phone }}</small>
                        @endif
                    </div>
                    <hr>
                @endif
                
                <div>
                    <strong>Thông tin giao hàng:</strong>
                    <address class="mt-2">
                        <strong>{{ $order->shipping_name }}</strong><br>
                        @if($order->shipping_phone)
                            <i class="fas fa-phone text-info"></i> {{ $order->shipping_phone }}<br>
                        @endif
                        @if($order->shipping_email)
                            <i class="fas fa-envelope text-info"></i> {{ $order->shipping_email }}<br>
                        @endif
                        <i class="fas fa-map-marker-alt text-danger"></i> {{ $order->shipping_address }}
                        @if($order->shipping_city), {{ $order->shipping_city }} @endif
                        @if($order->shipping_state), {{ $order->shipping_state }} @endif
                        @if($order->shipping_postal_code)<br>{{ $order->shipping_postal_code }}@endif
                    </address>
                </div>
                
                @if($order->notes)
                    <hr>
                    <div>
                        <strong>Ghi chú:</strong>
                        <p class="text-muted mt-1">{{ $order->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Order Summary -->
    <div class="col-lg-6 mb-4">
        <div class="card admin-card h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-receipt text-success"></i> Tóm tắt đơn hàng</h5>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col">Tạm tính ({{ $order->orderItems->count() }} sản phẩm):</div>
                    <div class="col-auto">{{ number_format($order->subtotal) }}₫</div>
                </div>
                
                @if($order->tax_amount > 0)
                    <div class="row mb-2">
                        <div class="col">Thuế:</div>
                        <div class="col-auto">{{ number_format($order->tax_amount) }}₫</div>
                    </div>
                @endif
                
                <div class="row mb-2">
                    <div class="col">Phí vận chuyển:</div>
                    <div class="col-auto">
                        @if($order->shipping_amount > 0)
                            {{ number_format($order->shipping_amount) }}₫
                        @else
                            <span class="text-success">Miễn phí</span>
                        @endif
                    </div>
                </div>
                
                @if($order->discount_amount > 0)
                    <div class="row mb-2 text-success">
                        <div class="col">Giảm giá:</div>
                        <div class="col-auto">-{{ number_format($order->discount_amount) }}₫</div>
                    </div>
                @endif
                
                <hr>
                <div class="row">
                    <div class="col"><strong>Tổng cộng:</strong></div>
                    <div class="col-auto"><strong class="text-success fs-5">{{ number_format($order->total_amount) }}₫</strong></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Order Items -->
    <div class="col-12">
        <div class="card admin-card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-box text-warning"></i> Sản phẩm trong đơn hàng</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Đơn giá</th>
                                <th>Số lượng</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->orderItems as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($item->product && $item->product->image)
                                                <img src="{{ asset('storage/' . $item->product->image) }}" 
                                                     class="img-thumbnail me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                            @else
                                                <img src="https://via.placeholder.com/60x60?text=No+Image" 
                                                     class="img-thumbnail me-3" style="width: 60px; height: 60px;">
                                            @endif
                                            <div>
                                                <h6 class="mb-0">{{ $item->product_name }}</h6>
                                                @if($item->product_sku)
                                                    <small class="text-muted">SKU: {{ $item->product_sku }}</small>
                                                @endif
                                                @if($item->product && !$item->product->is_active)
                                                    <br><span class="badge bg-warning">Sản phẩm đã ẩn</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <span class="fw-bold">{{ number_format($item->price) }}₫</span>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge bg-primary rounded-pill">{{ $item->quantity }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <span class="fw-bold text-success">{{ number_format($item->quantity * $item->price) }}₫</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .status-timeline {
        position: relative;
    }
    
    .timeline-item {
        display: flex;
        align-items: flex-start;
        margin-bottom: 20px;
        position: relative;
    }
    
    .timeline-item:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 20px;
        top: 45px;
        width: 2px;
        height: 25px;
        background: #e9ecef;
    }
    
    .timeline-item.completed::after,
    .timeline-item.active::after {
        background: #28a745;
    }
    
    .timeline-item.cancelled::after {
        background: #dc3545;
    }
    
    .timeline-marker {
        width: 40px;
        height: 40px;
        background: #e9ecef;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        color: #6c757d;
        flex-shrink: 0;
    }
    
    .timeline-item.completed .timeline-marker,
    .timeline-item.active .timeline-marker {
        background: #28a745;
        color: white;
    }
    
    .timeline-item.cancelled .timeline-marker {
        background: #dc3545;
        color: white;
    }
    
    .timeline-content h6 {
        margin-bottom: 5px;
        font-weight: 600;
    }
    
    .timeline-content small {
        color: #6c757d;
    }
</style>
@endpush

@push('scripts')
<script>
    function updateStatus(status) {
        const statusNames = {
            'processing': 'Đang xử lý',
            'shipped': 'Đã gửi hàng',
            'delivered': 'Đã giao hàng',
            'cancelled': 'Hủy đơn hàng'
        };
        
        if (confirm(`Bạn có chắc muốn cập nhật trạng thái đơn hàng thành "${statusNames[status]}"?`)) {
            fetch(`{{ route('admin.orders.update-status', $order) }}`, {
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
                    location.reload();
                } else {
                    alert(data.message || 'Có lỗi xảy ra khi cập nhật trạng thái');
                }
            })
            .catch(error => {
                alert('Có lỗi xảy ra khi cập nhật trạng thái');
            });
        }
    }
    
    function printInvoice() {
        window.open(`{{ route('orders.invoice', $order) }}`, '_blank');
    }
</script>
@endpush
