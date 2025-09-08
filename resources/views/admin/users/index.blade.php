@extends('admin.layout')

@section('title', 'Quản lý khách hàng')
@section('page-title', 'Quản lý khách hàng')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Khách hàng</li>
@endsection

@section('content')
<!-- Filters -->
<div class="card admin-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Tìm kiếm</label>
                <input type="text" class="form-control" name="search" value="{{ request('search') }}" 
                       placeholder="Tên, email, SĐT...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Vai trò</label>
                <select class="form-select" name="role">
                    <option value="">Tất cả</option>
                    <option value="admin" @selected(request('role') == 'admin')>Quản trị viên</option>
                    <option value="user" @selected(request('role') == 'user')>Khách hàng</option>
                </select>
            </div>
            <div class="col-md-5 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search"></i> Lọc
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Xóa lọc
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card admin-card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-list"></i> Danh sách khách hàng ({{ $users->total() }} khách hàng)
        </h5>
    </div>
    <div class="card-body p-0">
        @if($users->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0 table-admin">
                    <thead class="table-light">
                        <tr>
                            <th>Khách hàng</th>
                            <th>Email</th>
                            <th>Điện thoại</th>
                            <th>Vai trò</th>
                            <th>Đơn hàng</th>
                            <th>Tổng chi tiêu</th>
                            <th>Ngày đăng ký</th>
                            <th width="100">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-3">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $user->name }}</h6>
                                            @if($user->orders->isNotEmpty())
                                                <small class="text-muted">
                                                    Mua gần nhất: {{ $user->orders->first()->created_at->diffForHumans() }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-info">{{ $user->email }}</span>
                                    @if($user->email_verified_at)
                                        <br><small class="text-success">
                                            <i class="fas fa-check-circle"></i> Đã xác thực
                                        </small>
                                    @else
                                        <br><small class="text-warning">
                                            <i class="fas fa-exclamation-circle"></i> Chưa xác thực
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    @if($user->phone)
                                        <span class="text-muted">{{ $user->phone }}</span>
                                    @else
                                        <small class="text-muted">Chưa cập nhật</small>
                                    @endif
                                </td>
                                <td>
                                    @if($user->isAdmin())
                                        <span class="badge bg-danger">Quản trị viên</span>
                                    @else
                                        <span class="badge bg-primary">Khách hàng</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-center">
                                        <span class="fw-bold text-primary">{{ $user->orders->count() }}</span>
                                        <br><small class="text-muted">đơn hàng</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-center">
                                        @php
                                            $totalSpent = $user->orders()->where('payment_status', 'paid')->sum('total_amount');
                                        @endphp
                                        <span class="fw-bold text-success">{{ number_format($totalSpent) }}₫</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ $user->created_at->format('d/m/Y') }}</span>
                                    <br><small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.users.show', $user) }}" 
                                           class="btn btn-outline-primary" title="Chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($user->id !== auth()->id())
                                            <div class="dropdown">
                                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" 
                                                        data-bs-toggle="dropdown" title="Thay đổi vai trò">
                                                    <i class="fas fa-user-cog"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    @if(!$user->isAdmin())
                                                        <li><a class="dropdown-item role-change-btn" href="#" 
                                                               data-user-id="{{ $user->id }}" data-role="admin">
                                                            <i class="fas fa-crown text-danger"></i> Thăng cấp Admin</a></li>
                                                    @else
                                                        <li><a class="dropdown-item role-change-btn" href="#" 
                                                               data-user-id="{{ $user->id }}" data-role="user">
                                                            <i class="fas fa-user text-primary"></i> Hạ cấp User</a></li>
                                                    @endif
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5>Không tìm thấy khách hàng nào</h5>
                <p class="text-muted">Hãy thử thay đổi điều kiện lọc</p>
            </div>
        @endif
    </div>
    
    @if($users->hasPages())
        <div class="card-footer bg-white">
            {{ $users->appends(request()->query())->links() }}
        </div>
    @endif
</div>

@endsection

@push('styles')
<style>
    .avatar-circle {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
    }
</style>
@endpush

@push('scripts')
<script>
    // Debug: Check if required functions exist
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded');
        console.log('showConfirm function exists:', typeof window.showConfirm);
        console.log('showToast function exists:', typeof window.showToast);
        console.log('Bootstrap exists:', typeof bootstrap);
        
        // Add event listeners for role change buttons
        document.querySelectorAll('.role-change-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const userId = this.getAttribute('data-user-id');
                const role = this.getAttribute('data-role');
                console.log('Role change button clicked:', userId, role);
                updateRole(userId, role);
            });
        });
    });

    async function updateRole(userId, role) {
        console.log('updateRole called with:', userId, role);
        
        // Check if functions exist
        if (typeof window.showConfirm !== 'function') {
            alert('Lỗi: Function showConfirm không tồn tại');
            return;
        }
        
        const roleNames = {
            'admin': 'Quản trị viên',
            'user': 'Khách hàng'
        };
        
        try {
            const confirmed = await showConfirm(
                'Thay đổi vai trò',
                `Bạn có chắc muốn thay đổi vai trò người dùng thành "${roleNames[role]}"?`,
                'Thay đổi',
                'fas fa-user-cog text-primary',
                'btn-primary'
            );
            console.log('Confirmation result:', confirmed);
        
        if (confirmed) {
            const url = `{{ route('admin.users.update-role', '__USER_ID__') }}`.replace('__USER_ID__', userId);
            console.log('Making PATCH request to:', url);
            
            fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ role: role })
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}, statusText: ${response.statusText}`);
                }
                
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    if (typeof window.showToast === 'function') {
                        showToast('Thành công', 'Vai trò người dùng đã được cập nhật!', 'success');
                    } else {
                        alert('Vai trò người dùng đã được cập nhật!');
                    }
                    setTimeout(() => location.reload(), 1000);
                } else {
                    if (typeof window.showToast === 'function') {
                        showToast('Lỗi', data.message || 'Có lỗi xảy ra khi cập nhật vai trò', 'error');
                    } else {
                        alert('Lỗi: ' + (data.message || 'Có lỗi xảy ra khi cập nhật vai trò'));
                    }
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                if (typeof window.showToast === 'function') {
                    showToast('Lỗi', 'Có lỗi xảy ra khi cập nhật vai trò: ' + error.message, 'error');
                } else {
                    alert('Lỗi: Có lỗi xảy ra khi cập nhật vai trò: ' + error.message);
                }
            });
        }
        } catch (error) {
            console.error('Error in updateRole:', error);
            alert('Lỗi: ' + error.message);
        }
    }
</script>
@endpush
