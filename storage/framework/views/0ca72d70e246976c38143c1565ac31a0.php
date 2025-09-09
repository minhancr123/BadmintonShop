

<?php $__env->startSection('title', 'Chi tiết đơn hàng #' . ($order->order_number ?? $order->id) . ' - Badminton Shop'); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1>
                        <i class="fas fa-receipt"></i> 
                        Đơn hàng #<?php echo e($order->order_number ?? $order->id); ?>

                    </h1>
                    <p class="text-muted mb-0">Ngày đặt: <?php echo e($order->created_at->format('d/m/Y H:i')); ?></p>
                </div>
                <div class="text-end">
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
                    <span class="badge bg-<?php echo e($statusClass); ?> fs-6 mb-2"><?php echo e($statusText); ?></span><br>
                    <a href="<?php echo e(route('orders.index')); ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại danh sách
                    </a>
                </div>
            </div>

            <!-- Order Status Timeline -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-timeline"></i> Trạng thái đơn hàng
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item <?php echo e(in_array($order->status, ['pending', 'processing', 'shipped', 'delivered']) ? 'completed' : ''); ?>">
                            <div class="timeline-marker">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Đơn hàng đã được đặt</h6>
                                <p class="text-muted"><?php echo e($order->created_at->format('d/m/Y H:i')); ?></p>
                            </div>
                        </div>

                        <div class="timeline-item <?php echo e(in_array($order->status, ['processing', 'shipped', 'delivered']) ? 'completed' : ($order->status == 'pending' ? 'active' : '')); ?>">
                            <div class="timeline-marker">
                                <i class="fas fa-cog"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Đang xử lý</h6>
                                <p class="text-muted">Chuẩn bị hàng và đóng gói</p>
                            </div>
                        </div>

                        <div class="timeline-item <?php echo e(in_array($order->status, ['shipped', 'delivered']) ? 'completed' : ($order->status == 'processing' ? 'active' : '')); ?>">
                            <div class="timeline-marker">
                                <i class="fas fa-truck"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Đang giao hàng</h6>
                                <p class="text-muted">Hàng đang được vận chuyển đến bạn</p>
                            </div>
                        </div>

                        <div class="timeline-item <?php echo e($order->status == 'delivered' ? 'completed' : ($order->status == 'shipped' ? 'active' : '')); ?>">
                            <div class="timeline-marker">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Đã giao hàng</h6>
                                <p class="text-muted">
                                    <?php if($order->status == 'delivered'): ?>
                                        <?php echo e($order->updated_at->format('d/m/Y H:i')); ?>

                                    <?php else: ?>
                                        Chưa hoàn thành
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <?php if($order->status == 'cancelled'): ?>
                        <div class="timeline-item cancelled">
                            <div class="timeline-marker">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Đơn hàng đã bị hủy</h6>
                                <p class="text-muted"><?php echo e($order->updated_at->format('d/m/Y H:i')); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Order Items -->
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-box"></i> 
                                Sản phẩm (<?php echo e($order->orderItems->count()); ?> món)
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <?php $__currentLoopData = $order->orderItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="order-item d-flex align-items-center p-3 <?php echo e(!$loop->last ? 'border-bottom' : ''); ?>">
                                <div class="item-image me-3">
                                    <img src="<?php echo e($item->product && $item->product->image ? asset('storage/' . $item->product->image) : 'https://via.placeholder.com/80x80?text=No+Image'); ?>" 
                                         class="rounded" alt="<?php echo e($item->product->name ?? 'Product'); ?>" 
                                         style="width: 80px; height: 80px; object-fit: cover;">
                                </div>
                                <div class="item-details flex-grow-1">
                                    <h6 class="mb-1">
                                        <?php if($item->product): ?>
                                            <a href="<?php echo e(route('products.show', $item->product->slug)); ?>" class="text-decoration-none">
                                                <?php echo e($item->product->name); ?>

                                            </a>
                                        <?php else: ?>
                                            <?php echo e($item->product_name ?? 'Sản phẩm không tồn tại'); ?>

                                        <?php endif; ?>
                                    </h6>
                                    <?php if($item->product): ?>
                                        <small class="text-muted">SKU: <?php echo e($item->product->sku); ?></small><br>
                                        <small class="text-muted">Danh mục: <?php echo e($item->product->category->name ?? 'N/A'); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="item-quantity text-center me-4">
                                    <span class="d-block">Số lượng</span>
                                    <strong><?php echo e($item->quantity); ?></strong>
                                </div>
                                <div class="item-price text-center me-4">
                                    <span class="d-block">Đơn giá</span>
                                    <strong><?php echo e(number_format($item->price)); ?>₫</strong>
                                </div>
                                <div class="item-total text-end">
                                    <span class="d-block">Thành tiền</span>
                                    <strong class="text-primary fs-5"><?php echo e(number_format($item->total)); ?>₫</strong>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <!-- Order Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-cogs"></i> Thao tác
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex gap-2 flex-wrap">
                                <?php if($order->status == 'delivered'): ?>
                                    <button class="btn btn-success" onclick="reorderItems(<?php echo e($order->id); ?>)">
                                        <i class="fas fa-redo"></i> Đặt lại đơn hàng
                                    </button>
                                <?php endif; ?>
                                
                                <?php if(in_array($order->status, ['pending', 'processing'])): ?>
                                    <button class="btn btn-outline-danger" onclick="cancelOrder(<?php echo e($order->id); ?>)">
                                        <i class="fas fa-times"></i> Hủy đơn hàng
                                    </button>
                                <?php endif; ?>

                                <?php if($order->status == 'delivered'): ?>
                                    <a href="<?php echo e(route('orders.invoice', $order)); ?>" class="btn btn-outline-primary" target="_blank">
                                        <i class="fas fa-download"></i> Tải hóa đơn
                                    </a>
                                <?php endif; ?>

                                <button class="btn btn-outline-secondary" onclick="window.print()">
                                    <i class="fas fa-print"></i> In đơn hàng
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary & Info -->
                <div class="col-lg-4">
                    <!-- Payment & Shipping Info -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle"></i> Thông tin đơn hàng
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Payment Status -->
                            <div class="mb-3">
                                <h6>Thanh toán:</h6>
                                <div class="d-flex justify-content-between align-items-center">
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
                                    <span class="badge bg-<?php echo e($paymentStatusClass); ?>"><?php echo e($paymentStatusText); ?></span>
                                </div>
                            </div>

                            <!-- Shipping Info -->
                            <div class="mb-3">
                                <h6>Giao hàng đến:</h6>
                                <address class="mb-0">
                                    <strong><?php echo e($order->shipping_name); ?></strong><br>
                                    <?php echo e($order->shipping_phone); ?><br>
                                    <?php echo e($order->shipping_address); ?>

                                </address>
                            </div>

                            <?php if($order->notes): ?>
                            <!-- Order Notes -->
                            <div class="mb-3">
                                <h6>Ghi chú:</h6>
                                <p class="text-muted mb-0"><?php echo e($order->notes); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Order Total -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-calculator"></i> Tổng tiền
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php
                                $subtotal = $order->orderItems->sum('total');
                                $shipping = $subtotal >= 500000 ? 0 : 30000;
                            ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tạm tính:</span>
                                <span><?php echo e(number_format($subtotal)); ?>₫</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Phí vận chuyển:</span>
                                <span>
                                    <?php if($shipping == 0): ?>
                                        <span class="text-success">Miễn phí</span>
                                    <?php else: ?>
                                        <?php echo e(number_format($shipping)); ?>₫
                                    <?php endif; ?>
                                </span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <strong class="fs-5">Tổng cộng:</strong>
                                <strong class="text-primary fs-4"><?php echo e(number_format($order->total_amount)); ?>₫</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    height: 100%;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e9ecef;
    border: 3px solid #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    font-size: 12px;
    z-index: 1;
}

.timeline-item.completed .timeline-marker {
    background: #28a745;
    color: white;
}

.timeline-item.active .timeline-marker {
    background: #007bff;
    color: white;
}

.timeline-item.cancelled .timeline-marker {
    background: #dc3545;
    color: white;
}

.timeline-content h6 {
    margin-bottom: 0.25rem;
    color: #333;
}

.timeline-item.completed .timeline-content h6 {
    color: #28a745;
}

.timeline-item.active .timeline-content h6 {
    color: #007bff;
}

.timeline-item.cancelled .timeline-content h6 {
    color: #dc3545;
}

.order-item {
    transition: background-color 0.3s ease;
}

.order-item:hover {
    background-color: #f8f9fa;
}

@media (max-width: 768px) {
    .item-quantity,
    .item-price {
        display: none;
    }
    
    .item-details {
        font-size: 0.9rem;
    }
}

@media print {
    .btn, .card-header, nav, footer {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
async function cancelOrder(orderId) {
    const confirmed = await showConfirm(
        'Hủy đơn hàng',
        'Bạn có chắc chắn muốn hủy đơn hàng này? Hành động này không thể hoàn tác.',
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
        'Bạn có muốn thêm tất cả sản phẩm từ đơn hàng này vào giỏ hàng không?',
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\xampp\htdocs\BadmintonShop\resources\views/orders/show.blade.php ENDPATH**/ ?>