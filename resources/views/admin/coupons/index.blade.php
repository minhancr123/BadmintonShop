@extends('admin.layout')

@section('title', 'Mã giảm giá')
@section('page-title', 'Mã giảm giá')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Mã giảm giá</li>
@endsection

@section('page-actions')
    @if(Route::has('admin.coupons.create'))
        <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tạo mã mới
        </a>
    @endif
@endsection

@section('content')
<div class="row g-3">
    <div class="col-sm-6 col-xl-3">
        <div class="card admin-card h-100">
            <div class="card-body">
                <small class="text-muted text-uppercase">Tổng số mã</small>
                <div class="h3 mb-1">{{ number_format($summary['total']) }}</div>
                <small class="text-muted">Tất cả trạng thái</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card admin-card h-100">
            <div class="card-body">
                <small class="text-muted text-uppercase">Đang hoạt động</small>
                <div class="h3 mb-1 text-success">{{ number_format($summary['active']) }}</div>
                <small class="text-muted">Sẵn sàng sử dụng</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card admin-card h-100">
            <div class="card-body">
                <small class="text-muted text-uppercase">Đã lên lịch</small>
                <div class="h3 mb-1 text-info">{{ number_format($summary['scheduled']) }}</div>
                <small class="text-muted">Chưa tới thời gian áp dụng</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card admin-card h-100">
            <div class="card-body">
                <small class="text-muted text-uppercase">Đã hết hạn</small>
                <div class="h3 mb-1 text-danger">{{ number_format($summary['expired']) }}</div>
                <small class="text-muted">Cần gia hạn hoặc vô hiệu</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-sm-6 col-xl-3">
        <div class="card admin-card h-100">
            <div class="card-body">
                <small class="text-muted text-uppercase">Đang tắt</small>
                <div class="h3 mb-1 text-secondary">{{ number_format($summary['inactive']) }}</div>
                <small class="text-muted">Không cho phép sử dụng</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card admin-card h-100">
            <div class="card-body">
                <small class="text-muted text-uppercase">Lượt sử dụng hôm nay</small>
                <div class="h3 mb-1">{{ number_format($summary['usage_today']) }}</div>
                <small class="text-muted">Trong 24 giờ gần nhất</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card admin-card h-100">
            <div class="card-body">
                <small class="text-muted text-uppercase">Tổng lượt sử dụng</small>
                <div class="h3 mb-1">{{ number_format($summary['total_usage']) }}</div>
                <small class="text-muted">Đã được áp dụng</small>
            </div>
        </div>
    </div>
</div>

<div class="card admin-card mt-4">
    <div class="card-header bg-white">
        <form method="GET" action="{{ route('admin.coupons.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label for="search" class="form-label">Tìm kiếm</label>
                <input type="text" class="form-control" id="search" name="search" placeholder="Mã, tên chiến dịch hoặc mô tả" value="{{ $filters['search'] }}">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Trạng thái</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Tất cả</option>
                    <option value="active" @selected($filters['status'] === 'active')>Đang hoạt động</option>
                    <option value="inactive" @selected($filters['status'] === 'inactive')>Đang tắt</option>
                    <option value="scheduled" @selected($filters['status'] === 'scheduled')>Chờ kích hoạt</option>
                    <option value="expired" @selected($filters['status'] === 'expired')>Đã hết hạn</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="sort" class="form-label">Sắp xếp</label>
                <select class="form-select" id="sort" name="sort">
                    <option value="newest" @selected($filters['sort'] === 'newest')>Mới nhất</option>
                    <option value="ending_soon" @selected($filters['sort'] === 'ending_soon')>Sắp hết hạn</option>
                    <option value="popular" @selected($filters['sort'] === 'popular')>Lượt dùng cao</option>
                    <option value="value" @selected($filters['sort'] === 'value')>Giá trị giảm lớn</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter"></i> Lọc
                </button>
                <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary" title="Xóa bộ lọc">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        @if($coupons->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mã</th>
                            <th>Tên chiến dịch</th>
                            <th>Loại giảm</th>
                            <th>Giới hạn</th>
                            <th>Đơn tối thiểu</th>
                            <th>Bắt đầu</th>
                            <th>Kết thúc</th>
                            <th>Lượt dùng</th>
                            <th>Trạng thái</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($coupons as $coupon)
                            @php
                                $isActiveNow = $coupon->is_active && (is_null($coupon->starts_at) || $coupon->starts_at <= now()) && (is_null($coupon->ends_at) || $coupon->ends_at >= now());
                                $isScheduled = $coupon->is_active && $coupon->starts_at && $coupon->starts_at > now();
                                $isExpired = $coupon->ends_at && $coupon->ends_at < now();
                            @endphp
                            <tr>
                                <td class="fw-bold">{{ $coupon->code }}</td>
                                <td>{{ $coupon->name ?? '—' }}</td>
                                <td>
                                    @if($coupon->discount_type === 'percentage')
                                        {{ rtrim(rtrim(number_format($coupon->discount_value, 2), '0'), '.') }}%
                                    @else
                                        {{ number_format($coupon->discount_value) }}₫
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">
                                        Tổng: {{ $coupon->usage_limit ? number_format($coupon->usage_limit) : 'Không giới hạn' }}<br>
                                        Mỗi user: {{ $coupon->usage_limit_per_user ? number_format($coupon->usage_limit_per_user) : 'Không giới hạn' }}
                                    </small>
                                </td>
                                <td>{{ $coupon->minimum_order_amount ? number_format($coupon->minimum_order_amount) . '₫' : 'Không yêu cầu' }}</td>
                                <td>{{ $coupon->starts_at ? $coupon->starts_at->format('d/m/Y H:i') : 'Ngay lập tức' }}</td>
                                <td>{{ $coupon->ends_at ? $coupon->ends_at->format('d/m/Y H:i') : 'Không giới hạn' }}</td>
                                <td class="fw-bold text-success">{{ number_format($coupon->usages_count) }}</td>
                                <td>
                                    @if($isActiveNow)
                                        <span class="badge bg-success">Đang hoạt động</span>
                                    @elseif($isExpired)
                                        <span class="badge bg-danger">Đã hết hạn</span>
                                    @elseif($isScheduled)
                                        <span class="badge bg-info">Chờ kích hoạt</span>
                                    @elseif(!$coupon->is_active)
                                        <span class="badge bg-secondary">Đang tắt</span>
                                    @else
                                        <span class="badge bg-light text-dark">Không xác định</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex flex-wrap justify-content-end gap-2">
                                        <a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i> Sửa
                                        </a>

                                        @if(!$coupon->is_active || $isExpired || $isScheduled)
                                            <form method="POST" action="{{ route('admin.coupons.update-status', $coupon) }}" onsubmit="return confirm('Kích hoạt mã giảm giá này?');">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="activate">
                                                <button type="submit" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-toggle-on"></i> Kích hoạt
                                                </button>
                                            </form>
                                        @endif

                                        @if($coupon->is_active)
                                            <form method="POST" action="{{ route('admin.coupons.update-status', $coupon) }}" onsubmit="return confirm('Vô hiệu hóa mã giảm giá này?');">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="deactivate">
                                                <button type="submit" class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-toggle-off"></i> Tắt
                                                </button>
                                            </form>
                                        @endif

                                        <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" onsubmit="return confirm('Bạn chắc chắn muốn xóa mã giảm giá này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i> Xóa
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $coupons->links() }}
            </div>
        @else
            <div class="text-center py-5 text-muted">
                <i class="fas fa-ticket-alt fa-3x mb-3"></i>
                <p>Chưa có mã giảm giá nào.</p>
                @if(Route::has('admin.coupons.create'))
                    <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary">Tạo mã đầu tiên</a>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
