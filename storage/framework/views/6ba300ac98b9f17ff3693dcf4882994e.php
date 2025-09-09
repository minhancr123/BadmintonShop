

<?php $__env->startSection('title', 'Quản lý đơn hàng'); ?>
<?php $__env->startSection('page-title', 'Quản lý đơn hàng'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Dashboard</a></li>
    <li class="breadcrumb-item active">Đơn hàng</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<!-- Filters -->
<div class="card admin-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Tìm kiếm</label>
                <input type="text" class="form-control" name="search" value="<?php echo e(request('search')); ?>" 
                       placeholder="Mã đơn, khách hàng, SĐT...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Trạng thái</label>
                <select class="form-select" name="status">
                    <option value="">Tất cả</option>
                    <option value="pending" <?php if(request('status') == 'pending'): echo 'selected'; endif; ?>>Chờ xử lý</option>
                    <option value="processing" <?php if(request('status') == 'processing'): echo 'selected'; endif; ?>>Đang xử lý</option>
                    <option value="shipped" <?php if(request('status') == 'shipped'): echo 'selected'; endif; ?>>Đã gửi</option>
                    <option value="delivered" <?php if(request('status') == 'delivered'): echo 'selected'; endif; ?>>Đã giao</option>
                    <option value="cancelled" <?php if(request('status') == 'cancelled'): echo 'selected'; endif; ?>>Đã hủy</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Thanh toán</label>
                <select class="form-select" name="payment_status">
                    <option value="">Tất cả</option>
                    <option value="pending" <?php if(request('payment_status') == 'pending'): echo 'selected'; endif; ?>>Chờ thanh toán</option>
                    <option value="paid" <?php if(request('payment_status') == 'paid'): echo 'selected'; endif; ?>>Đã thanh toán</option>
                    <option value="failed" <?php if(request('payment_status') == 'failed'): echo 'selected'; endif; ?>>Thất bại</option>
                    <option value="refunded" <?php if(request('payment_status') == 'refunded'): echo 'selected'; endif; ?>>Đã hoàn tiền</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Từ ngày</label>
                <input type="date" class="form-control" name="date_from" value="<?php echo e(request('date_from')); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Đến ngày</label>
                <input type="date" class="form-control" name="date_to" value="<?php echo e(request('date_to')); ?>">
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
            <i class="fas fa-list"></i> Danh sách đơn hàng (<?php echo e($orders->total()); ?> đơn hàng)
        </h5>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('admin.orders.index')); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-refresh"></i> Làm mới
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if($orders->count() > 0): ?>
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
                        <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <div>
                                        <a href="<?php echo e(route('admin.orders.show', $order)); ?>" 
                                           class="fw-bold text-decoration-none text-primary">
                                            <?php echo e($order->order_number); ?>

                                        </a>
                                        <br><small class="text-muted"><?php echo e($order->orderItems->count()); ?> sản phẩm</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <h6 class="mb-0"><?php echo e($order->user->name ?? $order->shipping_name); ?></h6>
                                        <small class="text-muted">
                                            <?php if($order->user): ?>
                                                <?php echo e($order->user->email); ?>

                                            <?php endif; ?>
                                        </small>
                                        <?php if($order->shipping_phone): ?>
                                            <br><small class="text-info"><?php echo e($order->shipping_phone); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-success fs-6"><?php echo e(number_format($order->total_amount)); ?>₫</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php switch($order->status):
                                            case ('pending'): ?>
                                                <span class="badge bg-warning me-2">Chờ xử lý</span>
                                                <?php break; ?>
                                            <?php case ('processing'): ?>
                                                <span class="badge bg-info me-2">Đang xử lý</span>
                                                <?php break; ?>
                                            <?php case ('shipped'): ?>
                                                <span class="badge bg-primary me-2">Đã gửi</span>
                                                <?php break; ?>
                                            <?php case ('delivered'): ?>
                                                <span class="badge bg-success me-2">Đã giao</span>
                                                <?php break; ?>
                                            <?php case ('cancelled'): ?>
                                                <span class="badge bg-danger me-2">Đã hủy</span>
                                                <?php break; ?>
                                            <?php default: ?>
                                                <span class="badge bg-secondary me-2"><?php echo e($order->status); ?></span>
                                        <?php endswitch; ?>
                                        
                                        <?php if($order->status !== 'delivered' && $order->status !== 'cancelled'): ?>
                                            <div class="dropdown position-static">
                                                <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" 
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                                    <?php if($order->status === 'pending'): ?>
                                                        <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo e($order->id); ?>, 'processing')">
                                                            <i class="fas fa-cog text-info"></i> Đang xử lý</a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo e($order->id); ?>, 'shipped')">
                                                            <i class="fas fa-shipping-fast text-primary"></i> Đã gửi</a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo e($order->id); ?>, 'cancelled')">
                                                            <i class="fas fa-times text-danger"></i> Hủy đơn</a></li>
                                                    <?php elseif($order->status === 'processing'): ?>
                                                        <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo e($order->id); ?>, 'shipped')">
                                                            <i class="fas fa-shipping-fast text-primary"></i> Đã gửi</a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo e($order->id); ?>, 'cancelled')">
                                                            <i class="fas fa-times text-danger"></i> Hủy đơn</a></li>
                                                    <?php elseif($order->status === 'shipped'): ?>
                                                        <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo e($order->id); ?>, 'delivered')">
                                                            <i class="fas fa-check text-success"></i> Đã giao hàng</a></li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php switch($order->payment_status):
                                        case ('pending'): ?>
                                            <span class="badge bg-warning">Chờ thanh toán</span>
                                            <?php break; ?>
                                        <?php case ('paid'): ?>
                                            <span class="badge bg-success">Đã thanh toán</span>
                                            <?php break; ?>
                                        <?php case ('failed'): ?>
                                            <span class="badge bg-danger">Thất bại</span>
                                            <?php break; ?>
                                        <?php case ('refunded'): ?>
                                            <span class="badge bg-info">Đã hoàn tiền</span>
                                            <?php break; ?>
                                        <?php default: ?>
                                            <span class="badge bg-secondary"><?php echo e($order->payment_status); ?></span>
                                    <?php endswitch; ?>
                                    <?php if($order->payment_method): ?>
                                        <br><small class="text-muted"><?php echo e(ucfirst($order->payment_method)); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div>
                                        <span class="fw-bold"><?php echo e($order->created_at->format('d/m/Y')); ?></span>
                                        <br><small class="text-muted"><?php echo e($order->created_at->format('H:i')); ?></small>
                                        <br><small class="text-muted"><?php echo e($order->created_at->diffForHumans()); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="<?php echo e(route('admin.orders.show', $order)); ?>" 
                                           class="btn btn-outline-primary" title="Chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn btn-outline-info" 
                                                onclick="printInvoice(<?php echo e($order->id); ?>)" title="In hóa đơn">
                                            <i class="fas fa-print"></i>
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
                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                <h5>Không tìm thấy đơn hàng nào</h5>
                <p class="text-muted">Hãy thử thay đổi điều kiện lọc</p>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if($orders->hasPages()): ?>
        <div class="card-footer bg-white">
            <?php echo e($orders->appends(request()->query())->links()); ?>

        </div>
    <?php endif; ?>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
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
            fetch(`<?php echo e(route('admin.orders.update-status', ':orderId')); ?>`.replace(':orderId', orderId), {
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
        window.open(`<?php echo e(route('orders.invoice', ':orderId')); ?>`.replace(':orderId', orderId), '_blank');
    }

    // Auto refresh every 30 seconds for pending orders
    <?php if(request('status') === 'pending' || !request('status')): ?>
        setInterval(function() {
            if (document.visibilityState === 'visible') {
                location.reload();
            }
        }, 30000);
    <?php endif; ?>
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\xampp\htdocs\BadmintonShop\resources\views/admin/orders/index.blade.php ENDPATH**/ ?>