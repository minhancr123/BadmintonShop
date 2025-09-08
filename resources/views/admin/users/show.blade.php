@extends('admin.layout')

@section('title', 'Chi tiết người dùng')
@section('page-title', 'Chi tiết người dùng')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Người dùng</a></li>
    <li class="breadcrumb-item active">Chi tiết</li>
@endsection

@section('content')
<div class="row">
    <!-- User Information -->
    <div class="col-md-4">
        <div class="card admin-card">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-user"></i> Thông tin cá nhân
                </h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="avatar-lg mx-auto mb-3">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-user fa-2x text-white"></i>
                        </div>
                    </div>
                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <p class="text-muted mb-0">{{ $user->email }}</p>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td class="fw-bold">ID:</td>
                            <td>{{ $user->id }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Họ tên:</td>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Email:</td>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Số điện thoại:</td>
                            <td>{{ $user->phone ?? 'Chưa cập nhật' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Địa chỉ:</td>
                            <td>{{ $user->address ?? 'Chưa cập nhật' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Ngày đăng ký:</td>
                            <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Xác thực email:</td>
                            <td>
                                @if($user->email_verified_at)
                                    <span class="badge bg-success">Đã xác thực</span>
                                    <br><small class="text-muted">{{ $user->email_verified_at->format('d/m/Y H:i') }}</small>
                                @else
                                    <span class="badge bg-warning">Chưa xác thực</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Lần cuối online:</td>
                            <td>{{ $user->updated_at->diffForHumans() }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- User Statistics -->
    <div class="col-md-8">
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card admin-card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h3 class="mb-0">{{ $userStats['total_orders'] }}</h3>
                                <p class="mb-0">Tổng đơn hàng</p>
                            </div>
                            <div>
                                <i class="fas fa-shopping-cart fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card admin-card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h3 class="mb-0">{{ number_format($userStats['total_spent']) }}₫</h3>
                                <p class="mb-0">Tổng chi tiêu</p>
                            </div>
                            <div>
                                <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card admin-card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h3 class="mb-0">
                                    @if($userStats['last_order'])
                                        {{ $userStats['last_order']->created_at->diffForHumans() }}
                                    @else
                                        Chưa có
                                    @endif
                                </h3>
                                <p class="mb-0">Đơn hàng cuối</p>
                            </div>
                            <div>
                                <i class="fas fa-clock fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Order History -->
        <div class="card admin-card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-history"></i> Lịch sử đơn hàng
                </h5>
                <a href="{{ route('admin.orders.index', ['search' => $user->email]) }}" 
                   class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-external-link-alt"></i> Xem tất cả
                </a>
            </div>
            <div class="card-body p-0">
                @if($user->orders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã đơn hàng</th>
                                    <th>Ngày đặt</th>
                                    <th>Số sản phẩm</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Thanh toán</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->orders->take(10) as $order)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.orders.show', $order) }}" 
                                               class="fw-bold text-decoration-none">
                                                {{ $order->order_number }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ $order->created_at->format('d/m/Y') }}</span>
                                            <br><small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
                                        </td>
                                        <td>{{ $order->orderItems->count() }} sản phẩm</td>
                                        <td>
                                            <span class="fw-bold text-success">{{ number_format($order->total_amount) }}₫</span>
                                        </td>
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
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.orders.show', $order) }}" 
                                               class="btn btn-outline-primary btn-sm" title="Chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h5>Chưa có đơn hàng nào</h5>
                        <p class="text-muted">Người dùng này chưa thực hiện đơn hàng nào</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="row mt-4">
    <div class="col-12">
        <div class="d-flex justify-content-between">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
            
            <div class="btn-group">
                @if(!$user->email_verified_at)
                    <button class="btn btn-outline-success" onclick="verifyEmail({{ $user->id }})">
                        <i class="fas fa-check"></i> Xác thực email
                    </button>
                @endif
                
                <button class="btn btn-outline-warning" onclick="resetPassword({{ $user->id }})">
                    <i class="fas fa-key"></i> Reset mật khẩu
                </button>
                
                <button class="btn btn-outline-danger" onclick="suspendUser({{ $user->id }})">
                    <i class="fas fa-ban"></i> Tạm khóa
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
async function verifyEmail(userId) {
    const confirmed = await showConfirm(
        'Xác thực email',
        'Bạn có chắc muốn xác thực email cho người dùng này?',
        'Xác thực',
        'fas fa-check text-success',
        'btn-success'
    );
    
    if (confirmed) {
        // TODO: Implement verify email route
        // fetch(`/admin/users/${userId}/verify-email`, {
        alert('Email verification will be implemented soon');
        return;
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Thành công', 'Email đã được xác thực!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('Lỗi', data.message || 'Có lỗi xảy ra', 'error');
            }
        })
        .catch(error => {
            showToast('Lỗi', 'Có lỗi xảy ra khi xác thực email', 'error');
        });
    }
}

async function resetPassword(userId) {
    const confirmed = await showConfirm(
        'Reset mật khẩu',
        'Bạn có chắc muốn reset mật khẩu cho người dùng này? Mật khẩu mới sẽ được gửi qua email.',
        'Reset',
        'fas fa-key text-warning',
        'btn-warning'
    );
    
    if (confirmed) {
        // TODO: Implement reset password route
        // fetch(`/admin/users/${userId}/reset-password`, {
        alert('Password reset will be implemented soon');
        return;
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Thành công', 'Mật khẩu mới đã được gửi qua email', 'success');
            } else {
                showToast('Lỗi', data.message || 'Có lỗi xảy ra', 'error');
            }
        })
        .catch(error => {
            showToast('Lỗi', 'Có lỗi xảy ra khi reset mật khẩu', 'error');
        });
    }
}

async function suspendUser(userId) {
    const confirmed = await showConfirm(
        'Tạm khóa người dùng',
        'Bạn có chắc muốn tạm khóa người dùng này?',
        'Tạm khóa',
        'fas fa-ban text-danger',
        'btn-danger'
    );
    
    if (confirmed) {
        // TODO: Implement suspend user route
        // fetch(`/admin/users/${userId}/suspend`, {
        alert('User suspension will be implemented soon');
        return;
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Thành công', 'Người dùng đã được tạm khóa!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('Lỗi', data.message || 'Có lỗi xảy ra', 'error');
            }
        })
        .catch(error => {
            showToast('Lỗi', 'Có lỗi xảy ra khi tạm khóa người dùng', 'error');
        });
    }
}
</script>
@endpush
