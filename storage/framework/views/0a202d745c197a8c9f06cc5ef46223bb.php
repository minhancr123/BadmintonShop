

<?php $__env->startSection('title', 'Đơn hàng của tôi - Badminton Shop'); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1><i class="fas fa-clipboard-list"></i> Đơn hàng của tôi</h1>
                    <p class="text-muted mb-0">Theo dõi và quản lý các đơn hàng của bạn</p>
                </div>
                <a href="<?php echo e(route('products.index')); ?>" class="btn btn-primary">
                    <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
                </a>
            </div>

            <!-- Filter and Search -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="<?php echo e(route('orders.index')); ?>" class="row g-3">
                        <div class="col-md-4">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Tất cả trạng thái</option>
                                <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Chờ xử lý</option>
                                <option value="processing" <?php echo e(request('status') == 'processing' ? 'selected' : ''); ?>>Đang xử lý</option>
                                <option value="shipped" <?php echo e(request('status') == 'shipped' ? 'selected' : ''); ?>>Đã gửi</option>
                                <option value="delivered" <?php echo e(request('status') == 'delivered' ? 'selected' : ''); ?>>Đã giao</option>
                                <option value="cancelled" <?php echo e(request('status') == 'cancelled' ? 'selected' : ''); ?>>Đã hủy</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="date_from" class="form-label">Từ ngày</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo e(request('date_from')); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="date_to" class="form-label">Đến ngày</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo e(request('date_to')); ?>">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search"></i> Tìm kiếm
                            </button>
                            <a href="<?php echo e(route('orders.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Xóa bộ lọc
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Orders List -->
            <?php if($orders->count() > 0): ?>
                <div class="row">
                    <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-12 mb-4">
                        <div class="card order-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">
                                        <strong>Đơn hàng #<?php echo e($order->order_number ?? $order->id); ?></strong>
                                        <small class="text-muted ms-2"><?php echo e($order->created_at->format('d/m/Y H:i')); ?></small>
                                    </h6>
                                </div>
                                <div>
                                    <?php
                                        $statusClass = match($order->status) {
                                            'pending' => 'warning',
                                            'processing' => 'info',
                                            'shipped' => 'primary',
                                            'delivered' => 'success',
                                            'cancelled' => 'danger',
                                            default => 'secondary'
                                        };
                                        $statusText = match($order->status) {
                                            'pending' => 'Chờ xử lý',
                                            'processing' => 'Đang xử lý',
                                            'shipped' => 'Đã gửi',
                                            'delivered' => 'Đã giao',
                                            'cancelled' => 'Đã hủy',
                                            default => ucfirst($order->status)
                                        };
                                    ?>
                                    <span class="badge bg-<?php echo e($statusClass); ?>"><?php echo e($statusText); ?></span>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Order Items -->
                                <?php if($order->orderItems && $order->orderItems->count() > 0): ?>
                                <div class="order-items mb-3">
                                    <?php $__currentLoopData = $order->orderItems->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="d-flex align-items-center mb-2">
                                        <img src="<?php echo e($item->product && $item->product->image ? asset('storage/' . $item->product->image) : 'https://via.placeholder.com/60x60?text=No+Image'); ?>" 
                                             class="rounded me-3" alt="<?php echo e($item->product->name ?? 'Product'); ?>" style="width: 60px; height: 60px; object-fit: cover;">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?php echo e($item->product->name ?? 'Product not found'); ?></h6>
                                            <small class="text-muted">
                                                Số lượng: <?php echo e($item->quantity); ?> x <?php echo e(number_format($item->price)); ?>₫
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <strong><?php echo e(number_format($item->total)); ?>₫</strong>
                                        </div>
                                    </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    
                                    <?php if($order->orderItems->count() > 3): ?>
                                    <small class="text-muted">... và <?php echo e($order->orderItems->count() - 3); ?> sản phẩm khác</small>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>

                                <!-- Order Summary -->
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <div class="order-info">
                                            <p class="mb-1"><strong>Địa chỉ giao hàng:</strong></p>
                                            <p class="text-muted small mb-2">
                                                <?php echo e($order->shipping_name); ?><br>
                                                <?php echo e($order->shipping_phone); ?><br>
                                                <?php echo e($order->shipping_address); ?>

                                            </p>
                                            <p class="mb-1">
                                                <strong>Thanh toán:</strong> 
                                                <span class="text-capitalize"><?php echo e($order->payment_method ?? 'COD'); ?></span>
                                                <?php
                                                    $paymentStatusClass = match($order->payment_status ?? 'pending') {
                                                        'paid' => 'success',
                                                        'pending' => 'warning',
                                                        'failed' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                    $paymentStatusText = match($order->payment_status ?? 'pending') {
                                                        'paid' => 'Đã thanh toán',
                                                        'pending' => 'Chưa thanh toán',
                                                        'failed' => 'Thanh toán thất bại',
                                                        default => ucfirst($order->payment_status ?? 'pending')
                                                    };
                                                ?>
                                                <span class="badge bg-<?php echo e($paymentStatusClass); ?> ms-1"><?php echo e($paymentStatusText); ?></span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <div class="order-total mb-3">
                                            <h4 class="text-primary"><?php echo e(number_format($order->total_amount)); ?>₫</h4>
                                        </div>
                                        <div class="order-actions">
                                            <a href="<?php echo e(route('orders.show', $order)); ?>" class="btn btn-outline-primary btn-sm me-2">
                                                <i class="fas fa-eye"></i> Chi tiết
                                            </a>
                                            
                                            <?php if($order->status == 'delivered'): ?>
                                                <button class="btn btn-success btn-sm me-2" onclick="reorderItems(<?php echo e($order->id); ?>)">
                                                    <i class="fas fa-redo"></i> Đặt lại
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if(in_array($order->status, ['pending', 'processing'])): ?>
                                                <button class="btn btn-outline-danger btn-sm" onclick="cancelOrder(<?php echo e($order->id); ?>)">
                                                    <i class="fas fa-times"></i> Hủy
                                                </button>
                                            <?php endif; ?>

                                            <?php if($order->status == 'delivered'): ?>
                                                <a href="<?php echo e(route('orders.invoice', $order)); ?>" class="btn btn-outline-secondary btn-sm" target="_blank">
                                                    <i class="fas fa-download"></i> Hóa đơn
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <!-- Pagination -->
                <?php if($orders->hasPages()): ?>
                    <div class="d-flex justify-content-center">
                        <?php echo e($orders->appends(request()->query())->links()); ?>

                    </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- Empty State -->
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list fa-3x text-muted mb-4"></i>
                    <h4>Chưa có đơn hàng nào</h4>
                    <p class="text-muted mb-4">Bạn chưa có đơn hàng nào. Hãy bắt đầu mua sắm ngay!</p>
                    <a href="<?php echo e(route('products.index')); ?>" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-bag"></i> Bắt đầu mua sắm
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
async function cancelOrder(orderId) {
    const confirmed = await showConfirm(
        'Hủy đơn hàng',
        'Bạn có chắc chắn muốn hủy đơn hàng này?',
        'Hủy đơn',
        'fas fa-times text-danger',
        'btn-danger'
    );
    
    if (confirmed) {
        // Create a form to submit the cancellation request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/orders/${orderId}/cancel`;
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);
        
        document.body.appendChild(form);
        form.submit();
    }
}

async function reorderItems(orderId) {
    const confirmed = await showConfirm(
        'Đặt lại đơn hàng',
        'Bạn có muốn thêm tất cả sản phẩm từ đơn hàng này vào giỏ hàng?',
        'Thêm vào giỏ',
        'fas fa-shopping-cart text-success',
        'btn-success'
    );
    
    if (confirmed) {
        // Create a form to submit the reorder request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/orders/${orderId}/reorder`;
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.order-card {
    transition: box-shadow 0.3s ease;
}

.order-card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
}

.order-items {
    max-height: 300px;
    overflow-y: auto;
}

@media (max-width: 768px) {
    .order-actions .btn {
        margin-bottom: 0.5rem;
        width: 100%;
    }
}
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\badminton-shop\resources\views/orders/index.blade.php ENDPATH**/ ?>