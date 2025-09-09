

<?php $__env->startSection('title', 'Chi tiết đơn hàng #' . $order->order_number); ?>
<?php $__env->startSection('page-title', 'Chi tiết đơn hàng'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.orders.index')); ?>">Đơn hàng</a></li>
    <li class="breadcrumb-item active">#<?php echo e($order->order_number); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-actions'); ?>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-info btn-admin" onclick="printInvoice()">
            <i class="fas fa-print"></i> In hóa đơn
        </button>
        <?php if($order->status !== 'delivered' && $order->status !== 'cancelled'): ?>
            <div class="dropdown">
                <button class="btn btn-primary btn-admin dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-edit"></i> Cập nhật trạng thái
                </button>
                <ul class="dropdown-menu">
                    <?php if($order->status === 'pending'): ?>
                        <li><a class="dropdown-item" href="#" onclick="updateStatus('processing')">
                            <i class="fas fa-cog text-info"></i> Đang xử lý</a></li>
                        <li><a class="dropdown-item" href="#" onclick="updateStatus('shipped')">
                            <i class="fas fa-shipping-fast text-primary"></i> Đã gửi hàng</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" onclick="updateStatus('cancelled')">
                            <i class="fas fa-times text-danger"></i> Hủy đơn hàng</a></li>
                    <?php elseif($order->status === 'processing'): ?>
                        <li><a class="dropdown-item" href="#" onclick="updateStatus('shipped')">
                            <i class="fas fa-shipping-fast text-primary"></i> Đã gửi hàng</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" onclick="updateStatus('cancelled')">
                            <i class="fas fa-times text-danger"></i> Hủy đơn hàng</a></li>
                    <?php elseif($order->status === 'shipped'): ?>
                        <li><a class="dropdown-item" href="#" onclick="updateStatus('delivered')">
                            <i class="fas fa-check text-success"></i> Đã giao hàng</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <!-- Order Status & Timeline -->
    <div class="col-12 mb-4">
        <div class="card admin-card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="text-primary"><?php echo e($order->order_number); ?></h4>
                        <p class="text-muted mb-2">Đặt hàng: <?php echo e($order->created_at->format('d/m/Y H:i')); ?></p>
                        
                        <!-- Status Timeline -->
                        <div class="status-timeline mt-3">
                            <div class="timeline-item <?php echo e($order->status === 'pending' ? 'active' : ($order->created_at ? 'completed' : '')); ?>">
                                <div class="timeline-marker">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6>Đơn hàng được tạo</h6>
                                    <small><?php echo e($order->created_at->format('d/m/Y H:i')); ?></small>
                                </div>
                            </div>
                            
                            <div class="timeline-item <?php echo e($order->status === 'processing' ? 'active' : ($order->processing_at ? 'completed' : '')); ?>">
                                <div class="timeline-marker">
                                    <i class="fas fa-cog"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6>Đang xử lý</h6>
                                    <?php if($order->processing_at): ?>
                                        <small><?php echo e($order->processing_at->format('d/m/Y H:i')); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="timeline-item <?php echo e($order->status === 'shipped' ? 'active' : ($order->shipped_at ? 'completed' : '')); ?>">
                                <div class="timeline-marker">
                                    <i class="fas fa-shipping-fast"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6>Đã gửi hàng</h6>
                                    <?php if($order->shipped_at): ?>
                                        <small><?php echo e($order->shipped_at->format('d/m/Y H:i')); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="timeline-item <?php echo e($order->status === 'delivered' ? 'active completed' : ''); ?>">
                                <div class="timeline-marker">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6>Đã giao hàng</h6>
                                    <?php if($order->delivered_at): ?>
                                        <small><?php echo e($order->delivered_at->format('d/m/Y H:i')); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if($order->status === 'cancelled'): ?>
                                <div class="timeline-item cancelled">
                                    <div class="timeline-marker">
                                        <i class="fas fa-times"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h6>Đã hủy</h6>
                                        <?php if($order->cancelled_at): ?>
                                            <small><?php echo e($order->cancelled_at->format('d/m/Y H:i')); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6 text-md-end">
                        <div class="mb-3">
                            <?php switch($order->status):
                                case ('pending'): ?>
                                    <span class="badge bg-warning fs-6 px-3 py-2">Chờ xử lý</span>
                                    <?php break; ?>
                                <?php case ('processing'): ?>
                                    <span class="badge bg-info fs-6 px-3 py-2">Đang xử lý</span>
                                    <?php break; ?>
                                <?php case ('shipped'): ?>
                                    <span class="badge bg-primary fs-6 px-3 py-2">Đã gửi hàng</span>
                                    <?php break; ?>
                                <?php case ('delivered'): ?>
                                    <span class="badge bg-success fs-6 px-3 py-2">Đã giao hàng</span>
                                    <?php break; ?>
                                <?php case ('cancelled'): ?>
                                    <span class="badge bg-danger fs-6 px-3 py-2">Đã hủy</span>
                                    <?php break; ?>
                            <?php endswitch; ?>
                        </div>
                        
                        <div class="mb-3">
                            <?php switch($order->payment_status):
                                case ('pending'): ?>
                                    <span class="badge bg-warning">Chờ thanh toán</span>
                                    <?php break; ?>
                                <?php case ('paid'): ?>
                                    <span class="badge bg-success">Đã thanh toán</span>
                                    <?php break; ?>
                                <?php case ('failed'): ?>
                                    <span class="badge bg-danger">Thanh toán thất bại</span>
                                    <?php break; ?>
                                <?php case ('refunded'): ?>
                                    <span class="badge bg-info">Đã hoàn tiền</span>
                                    <?php break; ?>
                            <?php endswitch; ?>
                        </div>
                        
                        <h3 class="text-success mb-0"><?php echo e(number_format($order->total_amount)); ?>₫</h3>
                        <small class="text-muted">
                            <?php echo e($order->payment_method ? ucfirst($order->payment_method) : 'COD'); ?>

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
                <?php if($order->user): ?>
                    <div class="mb-3">
                        <strong>Tài khoản:</strong>
                        <p class="mb-1"><?php echo e($order->user->name); ?></p>
                        <small class="text-muted"><?php echo e($order->user->email); ?></small>
                        <?php if($order->user->phone): ?>
                            <br><small class="text-info"><?php echo e($order->user->phone); ?></small>
                        <?php endif; ?>
                    </div>
                    <hr>
                <?php endif; ?>
                
                <div>
                    <strong>Thông tin giao hàng:</strong>
                    <address class="mt-2">
                        <strong><?php echo e($order->shipping_name); ?></strong><br>
                        <?php if($order->shipping_phone): ?>
                            <i class="fas fa-phone text-info"></i> <?php echo e($order->shipping_phone); ?><br>
                        <?php endif; ?>
                        <?php if($order->shipping_email): ?>
                            <i class="fas fa-envelope text-info"></i> <?php echo e($order->shipping_email); ?><br>
                        <?php endif; ?>
                        <i class="fas fa-map-marker-alt text-danger"></i> <?php echo e($order->shipping_address); ?>

                        <?php if($order->shipping_city): ?>, <?php echo e($order->shipping_city); ?> <?php endif; ?>
                        <?php if($order->shipping_state): ?>, <?php echo e($order->shipping_state); ?> <?php endif; ?>
                        <?php if($order->shipping_postal_code): ?><br><?php echo e($order->shipping_postal_code); ?><?php endif; ?>
                    </address>
                </div>
                
                <?php if($order->notes): ?>
                    <hr>
                    <div>
                        <strong>Ghi chú:</strong>
                        <p class="text-muted mt-1"><?php echo e($order->notes); ?></p>
                    </div>
                <?php endif; ?>
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
                    <div class="col">Tạm tính (<?php echo e($order->orderItems->count()); ?> sản phẩm):</div>
                    <div class="col-auto"><?php echo e(number_format($order->subtotal)); ?>₫</div>
                </div>
                
                <?php if($order->tax_amount > 0): ?>
                    <div class="row mb-2">
                        <div class="col">Thuế:</div>
                        <div class="col-auto"><?php echo e(number_format($order->tax_amount)); ?>₫</div>
                    </div>
                <?php endif; ?>
                
                <div class="row mb-2">
                    <div class="col">Phí vận chuyển:</div>
                    <div class="col-auto">
                        <?php if($order->shipping_amount > 0): ?>
                            <?php echo e(number_format($order->shipping_amount)); ?>₫
                        <?php else: ?>
                            <span class="text-success">Miễn phí</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if($order->discount_amount > 0): ?>
                    <div class="row mb-2 text-success">
                        <div class="col">Giảm giá:</div>
                        <div class="col-auto">-<?php echo e(number_format($order->discount_amount)); ?>₫</div>
                    </div>
                <?php endif; ?>
                
                <hr>
                <div class="row">
                    <div class="col"><strong>Tổng cộng:</strong></div>
                    <div class="col-auto"><strong class="text-success fs-5"><?php echo e(number_format($order->total_amount)); ?>₫</strong></div>
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
                            <?php $__currentLoopData = $order->orderItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if($item->product && $item->product->image): ?>
                                                <img src="<?php echo e(asset('storage/' . $item->product->image)); ?>" 
                                                     class="img-thumbnail me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                            <?php else: ?>
                                                <img src="https://via.placeholder.com/60x60?text=No+Image" 
                                                     class="img-thumbnail me-3" style="width: 60px; height: 60px;">
                                            <?php endif; ?>
                                            <div>
                                                <h6 class="mb-0">
                                                    <?php if($item->product): ?>
                                                        <?php echo e($item->product->name); ?>  
                                                    <?php else: ?> 
                                                        <?php echo e($item->product_name ?? 'Sản phẩm đã xóa'); ?>

                                                    <?php endif; ?>
                                                </h6>
                                                <?php if($item->product_sku): ?>
                                                    <small class="text-muted">SKU: <?php echo e($item->product_sku); ?></small>
                                                <?php endif; ?>
                                                <?php if($item->product && !$item->product->is_active): ?>
                                                    <br><span class="badge bg-warning">Sản phẩm đã ẩn</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <span class="fw-bold"><?php echo e(number_format($item->price)); ?>₫</span>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge bg-primary rounded-pill"><?php echo e($item->quantity); ?></span>
                                    </td>
                                    <td class="align-middle">
                                        <span class="fw-bold text-success"><?php echo e(number_format($item->quantity * $item->price)); ?>₫</span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
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
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    function updateStatus(status) {
        const statusNames = {
            'processing': 'Đang xử lý',
            'shipped': 'Đã gửi hàng',
            'delivered': 'Đã giao hàng',
            'cancelled': 'Hủy đơn hàng'
        };
        
        showConfirm(
            'Cập nhật trạng thái',
            `Bạn có chắc muốn cập nhật trạng thái đơn hàng thành "${statusNames[status]}"?`,
            'Cập nhật'
        ).then((confirmed) => {
            if (confirmed) {
                fetch(`<?php echo e(route('admin.orders.update-status', $order)); ?>`, {
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
        });
    }
    
    function printInvoice() {
        window.open(`<?php echo e(route('orders.invoice', $order)); ?>`, '_blank');
    }
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\xampp\htdocs\BadmintonShop\resources\views/admin/orders/show.blade.php ENDPATH**/ ?>