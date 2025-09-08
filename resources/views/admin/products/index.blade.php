@extends('admin.layout')

@section('title', 'Quản lý sản phẩm')
@section('page-title', 'Quản lý sản phẩm')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Sản phẩm</li>
@endsection

@section('page-actions')
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-admin">
        <i class="fas fa-plus"></i> Thêm sản phẩm mới
    </a>
@endsection

@section('content')
<!-- Filters -->
<div class="card admin-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Tìm kiếm</label>
                <input type="text" class="form-control" name="search" value="{{ request('search') }}" 
                       placeholder="Tên, SKU, thương hiệu...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Danh mục</label>
                <select class="form-select" name="category">
                    <option value="">Tất cả</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category') == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Trạng thái</label>
                <select class="form-select" name="status">
                    <option value="">Tất cả</option>
                    <option value="active" @selected(request('status') == 'active')>Hoạt động</option>
                    <option value="inactive" @selected(request('status') == 'inactive')>Không hoạt động</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Sắp xếp</label>
                <select class="form-select" name="sort">
                    <option value="newest" @selected(request('sort') == 'newest')>Mới nhất</option>
                    <option value="name" @selected(request('sort') == 'name')>Theo tên</option>
                    <option value="price_asc" @selected(request('sort') == 'price_asc')>Giá tăng dần</option>
                    <option value="price_desc" @selected(request('sort') == 'price_desc')>Giá giảm dần</option>
                    <option value="stock" @selected(request('sort') == 'stock')>Tồn kho thấp</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search"></i> Lọc
                </button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Xóa lọc
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Products Table -->
<div class="card admin-card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-list"></i> Danh sách sản phẩm ({{ $products->total() }} sản phẩm)
        </h5>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-success btn-sm" onclick="bulkAction('activate')">
                <i class="fas fa-check"></i> Kích hoạt
            </button>
            <button class="btn btn-outline-warning btn-sm" onclick="bulkAction('deactivate')">
                <i class="fas fa-pause"></i> Vô hiệu
            </button>
            <button class="btn btn-outline-danger btn-sm" onclick="bulkAction('delete')">
                <i class="fas fa-trash"></i> Xóa
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        @if($products->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0 table-admin">
                    <thead class="table-light">
                        <tr>
                            <th width="50">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th width="80">Hình ảnh</th>
                            <th>Sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Giá</th>
                            <th>Tồn kho</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th width="120">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input product-checkbox" value="{{ $product->id }}">
                                </td>
                                <td>
                                    <img src="{{ $product->image ? asset('storage/' . $product->image) : 'https://via.placeholder.com/60x60?text=No+Image' }}" 
                                         class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;" 
                                         alt="{{ $product->name }}">
                                </td>
                                <td>
                                    <div>
                                        <h6 class="mb-0">{{ Str::limit($product->name, 40) }}</h6>
                                        <small class="text-muted">SKU: {{ $product->sku }}</small>
                                        @if($product->brand)
                                            <br><small class="text-info">{{ $product->brand }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $product->category->name ?? 'Chưa phân loại' }}</span>
                                </td>
                                <td>
                                    @if($product->is_on_sale && $product->sale_price)
                                        <div>
                                            <span class="text-danger fw-bold">{{ number_format($product->sale_price) }}₫</span>
                                            <br><small class="text-decoration-line-through text-muted">{{ number_format($product->price) }}₫</small>
                                        </div>
                                    @else
                                        <span class="fw-bold">{{ number_format($product->price) }}₫</span>
                                    @endif
                                </td>
                                <td>
                                    @if($product->quantity <= 0)
                                        <span class="badge bg-danger">Hết hàng</span>
                                    @elseif($product->quantity <= 5)
                                        <span class="badge bg-warning">{{ $product->quantity }} còn lại</span>
                                    @else
                                        <span class="badge bg-success">{{ $product->quantity }} có sẵn</span>
                                    @endif
                                </td>
                                <td>
                                    @if($product->is_active)
                                        <span class="badge bg-success">Hoạt động</span>
                                    @else
                                        <span class="badge bg-secondary">Ẩn</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $product->created_at->format('d/m/Y') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.products.edit', $product) }}" 
                                           class="btn btn-outline-primary" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('products.show', $product->slug) }}" 
                                           class="btn btn-outline-info" target="_blank" title="Xem">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn btn-outline-danger" 
                                                onclick="deleteProduct({{ $product->id }})" title="Xóa">
                                            <i class="fas fa-trash"></i>
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
                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                <h5>Không tìm thấy sản phẩm nào</h5>
                <p class="text-muted">Hãy thử thay đổi điều kiện lọc hoặc <a href="{{ route('admin.products.create') }}">thêm sản phẩm mới</a></p>
            </div>
        @endif
    </div>
    
    @if($products->hasPages())
        <div class="card-footer bg-white">
            {{ $products->appends(request()->query())->links() }}
        </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
    // Select All Checkbox
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllBtn = document.getElementById('selectAll');
        if (selectAllBtn) {
            selectAllBtn.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.product-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
        }
    });

    // Delete Product
    async function deleteProduct(productId) {
        const confirmed = await window.showConfirm(
            'Xóa sản phẩm',
            'Bạn có chắc chắn muốn xóa sản phẩm này? Hành động này không thể hoàn tác!',
            'Xóa',
            'fas fa-trash text-danger',
            'btn-danger'
        );
        
        if (confirmed) {
            fetch(`{{ route('admin.products.destroy', ':productId') }}`.replace(':productId', productId), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.showToast('Thành công', 'Sản phẩm đã được xóa!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    window.showToast('Lỗi', 'Có lỗi xảy ra khi xóa sản phẩm', 'error');
                }
            })
            .catch(error => {
                window.showToast('Lỗi', 'Có lỗi xảy ra khi xóa sản phẩm', 'error');
            });
        }
    }

    // Bulk Actions
    async function bulkAction(action) {
        const selectedProducts = Array.from(document.querySelectorAll('.product-checkbox:checked'))
            .map(checkbox => checkbox.value);
        
        if (selectedProducts.length === 0) {
            window.showToast('Thông báo', 'Vui lòng chọn ít nhất một sản phẩm', 'warning');
            return;
        }

        let title = '';
        let message = '';
        let btnText = '';
        let iconClass = '';
        let btnClass = '';

        switch(action) {
            case 'activate':
                title = 'Kích hoạt sản phẩm';
                message = `Bạn có chắc muốn kích hoạt ${selectedProducts.length} sản phẩm đã chọn?`;
                btnText = 'Kích hoạt';
                iconClass = 'fas fa-check text-success';
                btnClass = 'btn-success';
                break;
            case 'deactivate':
                title = 'Vô hiệu hóa sản phẩm';
                message = `Bạn có chắc muốn vô hiệu hóa ${selectedProducts.length} sản phẩm đã chọn?`;
                btnText = 'Vô hiệu hóa';
                iconClass = 'fas fa-pause text-warning';
                btnClass = 'btn-warning';
                break;
            case 'delete':
                title = 'Xóa sản phẩm';
                message = `Bạn có chắc muốn xóa ${selectedProducts.length} sản phẩm đã chọn? Hành động này không thể hoàn tác!`;
                btnText = 'Xóa tất cả';
                iconClass = 'fas fa-trash text-danger';
                btnClass = 'btn-danger';
                break;
        }

        const confirmed = await window.showConfirm(title, message, btnText, iconClass, btnClass);
        
        if (confirmed) {
            const formData = new FormData();
            formData.append('action', action);
            formData.append('products', JSON.stringify(selectedProducts));

            fetch('{{ route("admin.products.bulk-action") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.showToast('Thành công', data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    window.showToast('Lỗi', data.message || 'Có lỗi xảy ra khi thực hiện thao tác', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                window.showToast('Lỗi', 'Có lỗi xảy ra khi thực hiện thao tác', 'error');
            });
        }
    }
</script>
@endpush
