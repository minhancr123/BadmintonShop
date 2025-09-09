

<?php $__env->startSection('title', 'Quản lý sản phẩm'); ?>
<?php $__env->startSection('page-title', 'Quản lý sản phẩm'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Dashboard</a></li>
    <li class="breadcrumb-item active">Sản phẩm</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-actions'); ?>
    <a href="<?php echo e(route('admin.products.create')); ?>" class="btn btn-primary btn-admin">
        <i class="fas fa-plus"></i> Thêm sản phẩm mới
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<!-- Filters -->
<div class="card admin-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Tìm kiếm</label>
                <input type="text" class="form-control" name="search" value="<?php echo e(request('search')); ?>" 
                       placeholder="Tên, SKU, thương hiệu...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Danh mục</label>
                <select class="form-select" name="category">
                    <option value="">Tất cả</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($category->id); ?>" <?php if(request('category') == $category->id): echo 'selected'; endif; ?>>
                            <?php echo e($category->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Trạng thái</label>
                <select class="form-select" name="status">
                    <option value="">Tất cả</option>
                    <option value="active" <?php if(request('status') == 'active'): echo 'selected'; endif; ?>>Hoạt động</option>
                    <option value="inactive" <?php if(request('status') == 'inactive'): echo 'selected'; endif; ?>>Không hoạt động</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Sắp xếp</label>
                <select class="form-select" name="sort">
                    <option value="newest" <?php if(request('sort') == 'newest'): echo 'selected'; endif; ?>>Mới nhất</option>
                    <option value="name" <?php if(request('sort') == 'name'): echo 'selected'; endif; ?>>Theo tên</option>
                    <option value="price_asc" <?php if(request('sort') == 'price_asc'): echo 'selected'; endif; ?>>Giá tăng dần</option>
                    <option value="price_desc" <?php if(request('sort') == 'price_desc'): echo 'selected'; endif; ?>>Giá giảm dần</option>
                    <option value="stock" <?php if(request('sort') == 'stock'): echo 'selected'; endif; ?>>Tồn kho thấp</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search"></i> Lọc
                </button>
                <a href="<?php echo e(route('admin.products.index')); ?>" class="btn btn-outline-secondary">
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
            <i class="fas fa-list"></i> Danh sách sản phẩm (<?php echo e($products->total()); ?> sản phẩm)
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
        <?php if($products->count() > 0): ?>
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
                        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input product-checkbox" value="<?php echo e($product->id); ?>">
                                </td>
                                <td>
                                    <img src="<?php echo e($product->image ? asset('storage/' . $product->image) : 'https://via.placeholder.com/60x60?text=No+Image'); ?>" 
                                         class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;" 
                                         alt="<?php echo e($product->name); ?>">
                                </td>
                                <td>
                                    <div>
                                        <h6 class="mb-0"><?php echo e(Str::limit($product->name, 40)); ?></h6>
                                        <small class="text-muted">SKU: <?php echo e($product->sku); ?></small>
                                        <?php if($product->brand): ?>
                                            <br><small class="text-info"><?php echo e($product->brand); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark"><?php echo e($product->category->name ?? 'Chưa phân loại'); ?></span>
                                </td>
                                <td>
                                    <?php if($product->is_on_sale && $product->sale_price): ?>
                                        <div>
                                            <span class="text-danger fw-bold"><?php echo e(number_format($product->sale_price)); ?>₫</span>
                                            <br><small class="text-decoration-line-through text-muted"><?php echo e(number_format($product->price)); ?>₫</small>
                                        </div>
                                    <?php else: ?>
                                        <span class="fw-bold"><?php echo e(number_format($product->price)); ?>₫</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($product->quantity <= 0): ?>
                                        <span class="badge bg-danger">Hết hàng</span>
                                    <?php elseif($product->quantity <= 5): ?>
                                        <span class="badge bg-warning"><?php echo e($product->quantity); ?> còn lại</span>
                                    <?php else: ?>
                                        <span class="badge bg-success"><?php echo e($product->quantity); ?> có sẵn</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($product->is_active): ?>
                                        <span class="badge bg-success">Hoạt động</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Ẩn</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted"><?php echo e($product->created_at->format('d/m/Y')); ?></small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="<?php echo e(route('admin.products.edit', $product)); ?>" 
                                           class="btn btn-outline-primary" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo e(route('products.show', $product->slug)); ?>" 
                                           class="btn btn-outline-info" target="_blank" title="Xem">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn btn-outline-danger" 
                                                onclick="deleteProduct(<?php echo e($product->id); ?>)" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                <h5>Không tìm thấy sản phẩm nào</h5>
                <p class="text-muted">Hãy thử thay đổi điều kiện lọc hoặc <a href="<?php echo e(route('admin.products.create')); ?>">thêm sản phẩm mới</a></p>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if($products->hasPages()): ?>
        <div class="card-footer bg-white">
            <?php echo e($products->appends(request()->query())->links()); ?>

        </div>
    <?php endif; ?>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
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
            fetch(`<?php echo e(route('admin.products.destroy', ':productId')); ?>`.replace(':productId', productId), {
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

            fetch('<?php echo e(route("admin.products.bulk-action")); ?>', {
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
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\xampp\htdocs\BadmintonShop\resources\views/admin/products/index.blade.php ENDPATH**/ ?>