@extends('admin.layout')

@section('title', 'Quản lý danh mục')
@section('page-title', 'Quản lý danh mục')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Danh mục</li>
@endsection

@section('page-actions')
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-admin">
        <i class="fas fa-plus"></i> Thêm danh mục mới
    </a>
@endsection

@section('content')
<!-- Filters -->
<div class="card admin-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Tìm kiếm</label>
                <input type="text" class="form-control" name="search" value="{{ request('search') }}" 
                       placeholder="Tên danh mục...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Trạng thái</label>
                <select class="form-select" name="status">
                    <option value="">Tất cả</option>
                    <option value="active" @selected(request('status') == 'active')>Hoạt động</option>
                    <option value="inactive" @selected(request('status') == 'inactive')>Không hoạt động</option>
                </select>
            </div>
            <div class="col-md-5 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search"></i> Lọc
                </button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Xóa lọc
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Categories Table -->
<div class="card admin-card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-list"></i> Danh sách danh mục ({{ $categories->total() }} danh mục)
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
        @if($categories->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0 table-admin">
                    <thead class="table-light">
                        <tr>
                            <th width="50">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th width="80">Hình ảnh</th>
                            <th>Tên danh mục</th>
                            <th>Slug</th>
                            <th>Số sản phẩm</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th width="120">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input category-checkbox" value="{{ $category->id }}">
                                </td>
                                <td>
                                    <img src="{{ $category->image ? asset('storage/' . $category->image) : 'https://via.placeholder.com/60x60?text=No+Image' }}" 
                                         class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;" 
                                         alt="{{ $category->name }}">
                                </td>
                                <td>
                                    <div>
                                        <h6 class="mb-0">{{ $category->name }}</h6>
                                        @if($category->description)
                                            <small class="text-muted">{{ Str::limit($category->description, 50) }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <code class="text-info">{{ $category->slug }}</code>
                                </td>
                                <td>
                                    <span class="badge bg-primary rounded-pill">
                                        {{ $category->products_count }} sản phẩm
                                    </span>
                                </td>
                                <td>
                                    @if($category->is_active)
                                        <span class="badge bg-success">Hoạt động</span>
                                    @else
                                        <span class="badge bg-secondary">Ẩn</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $category->created_at->format('d/m/Y') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.categories.edit', $category) }}" 
                                           class="btn btn-outline-primary" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('categories.show', $category->slug) }}" 
                                           class="btn btn-outline-info" target="_blank" title="Xem">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($category->products_count == 0)
                                            <button class="btn btn-outline-danger" 
                                                    onclick="deleteCategory({{ $category->id }})" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @else
                                            <button class="btn btn-outline-secondary" disabled 
                                                    title="Không thể xóa danh mục có sản phẩm">
                                                <i class="fas fa-lock"></i>
                                            </button>
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
                <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                <h5>Không tìm thấy danh mục nào</h5>
                <p class="text-muted">Hãy thử thay đổi điều kiện lọc hoặc <a href="{{ route('admin.categories.create') }}">thêm danh mục mới</a></p>
            </div>
        @endif
    </div>
    
    @if($categories->hasPages())
        <div class="card-footer bg-white">
            {{ $categories->appends(request()->query())->links() }}
        </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
    // Select All Checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.category-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Delete Category
    async function deleteCategory(categoryId) {
        const confirmed = await window.showConfirm(
            'Xóa danh mục',
            'Bạn có chắc chắn muốn xóa danh mục này?',
            'Xóa',
            'fas fa-trash text-danger',
            'btn-danger'
        );
        
        if (confirmed) {
            fetch(`{{ route('admin.categories.destroy', ':categoryId') }}`.replace(':categoryId', categoryId), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.showToast('Thành công', data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    window.showToast('Lỗi', data.message || 'Có lỗi xảy ra khi xóa danh mục', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                window.showToast('Lỗi', 'Có lỗi xảy ra khi xóa danh mục', 'error');
            });
        }
    }

    // Bulk Actions
    async function bulkAction(action) {
        const selectedCategories = Array.from(document.querySelectorAll('.category-checkbox:checked'))
            .map(checkbox => checkbox.value);
        
        if (selectedCategories.length === 0) {
            showToast('Thông báo', 'Vui lòng chọn ít nhất một danh mục', 'warning');
            return;
        }

        let title = '';
        let message = '';
        let btnText = '';
        let iconClass = '';
        let btnClass = '';

        switch(action) {
            case 'activate':
                title = 'Kích hoạt danh mục';
                message = `Bạn có chắc muốn kích hoạt ${selectedCategories.length} danh mục đã chọn?`;
                btnText = 'Kích hoạt';
                iconClass = 'fas fa-check text-success';
                btnClass = 'btn-success';
                break;
            case 'deactivate':
                title = 'Vô hiệu hóa danh mục';
                message = `Bạn có chắc muốn vô hiệu hóa ${selectedCategories.length} danh mục đã chọn?`;
                btnText = 'Vô hiệu hóa';
                iconClass = 'fas fa-pause text-warning';
                btnClass = 'btn-warning';
                break;
            case 'delete':
                title = 'Xóa danh mục';
                message = `Bạn có chắc muốn xóa ${selectedCategories.length} danh mục đã chọn? Hành động này không thể hoàn tác!`;
                btnText = 'Xóa tất cả';
                iconClass = 'fas fa-trash text-danger';
                btnClass = 'btn-danger';
                break;
        }

        const confirmed = await window.showConfirm(title, message, btnText, iconClass, btnClass);

        if (confirmed) {
            const formData = new FormData();
            formData.append('action', action);
            formData.append('categories', JSON.stringify(selectedCategories));

            fetch('{{ route("admin.categories.bulk-action") }}', {
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
