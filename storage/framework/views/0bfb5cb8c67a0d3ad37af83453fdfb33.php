

<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('page-title', 'Dashboard'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item active">Dashboard</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card admin-card stats-card">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="text-uppercase text-white-50 fw-bold small">Sản phẩm</div>
                        <div class="h2 mb-0 text-white"><?php echo e(number_format($stats['total_products'])); ?></div>
                        <small class="text-white-50"><?php echo e($stats['active_products']); ?> đang hoạt động</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-box fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card admin-card stats-card success">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="text-uppercase text-white-50 fw-bold small">Đơn hàng</div>
                        <div class="h2 mb-0 text-white"><?php echo e(number_format($stats['total_orders'])); ?></div>
                        <small class="text-white-50"><?php echo e($stats['pending_orders']); ?> chờ xử lý</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-cart fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card admin-card stats-card warning">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="text-uppercase text-white-50 fw-bold small">Khách hàng</div>
                        <div class="h2 mb-0 text-white"><?php echo e(number_format($stats['total_users'])); ?></div>
                        <small class="text-white-50">Người dùng đã đăng ký</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card admin-card stats-card info">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="text-uppercase fw-bold small">Doanh thu</div>
                        <div class="h2 mb-0"><?php echo e(number_format($stats['total_revenue'])); ?>₫</div>
                        <small class="text-muted">Tổng doanh thu</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Sales Chart -->
    <div class="col-xl-8 mb-4">
        <div class="card admin-card">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-chart-area text-primary"></i> Biểu đồ doanh số (6 tháng gần đây)
                </h5>
            </div>
            <div class="card-body">
                <canvas id="salesChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-xl-4 mb-4">
        <div class="card admin-card">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-rocket text-success"></i> Thao tác nhanh
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?php echo e(route('admin.products.create')); ?>" class="btn btn-primary btn-admin">
                        <i class="fas fa-plus"></i> Thêm sản phẩm mới
                    </a>
                    <a href="<?php echo e(route('admin.categories.create')); ?>" class="btn btn-info btn-admin">
                        <i class="fas fa-tag"></i> Thêm danh mục mới
                    </a>
                    <a href="<?php echo e(route('admin.orders.index')); ?>" class="btn btn-warning btn-admin">
                        <i class="fas fa-eye"></i> Xem đơn hàng chờ
                    </a>
                    <a href="<?php echo e(route('admin.reports')); ?>" class="btn btn-secondary btn-admin">
                        <i class="fas fa-chart-bar"></i> Xem báo cáo
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Orders -->
    <div class="col-xl-7 mb-4">
        <div class="card admin-card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-clipboard-list text-info"></i> Đơn hàng gần đây
                </h5>
                <a href="<?php echo e(route('admin.orders.index')); ?>" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
            </div>
            <div class="card-body p-0">
                <?php if($recentOrders->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Khách hàng</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Thời gian</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo e(route('admin.orders.show', $order)); ?>" class="text-decoration-none fw-bold">
                                                <?php echo e($order->order_number); ?>

                                            </a>
                                        </td>
                                        <td><?php echo e($order->user->name ?? $order->shipping_name); ?></td>
                                        <td class="fw-bold text-success"><?php echo e(number_format($order->total_amount)); ?>₫</td>
                                        <td>
                                            <?php switch($order->status):
                                                case ('pending'): ?>
                                                    <span class="badge bg-warning">Chờ xử lý</span>
                                                    <?php break; ?>
                                                <?php case ('processing'): ?>
                                                    <span class="badge bg-info">Đang xử lý</span>
                                                    <?php break; ?>
                                                <?php case ('shipped'): ?>
                                                    <span class="badge bg-primary">Đã gửi</span>
                                                    <?php break; ?>
                                                <?php case ('delivered'): ?>
                                                    <span class="badge bg-success">Đã giao</span>
                                                    <?php break; ?>
                                                <?php case ('cancelled'): ?>
                                                    <span class="badge bg-danger">Đã hủy</span>
                                                    <?php break; ?>
                                                <?php default: ?>
                                                    <span class="badge bg-secondary"><?php echo e($order->status); ?></span>
                                            <?php endswitch; ?>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?php echo e($order->created_at->diffForHumans()); ?></small>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <p>Chưa có đơn hàng nào</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Low Stock Products -->
    <div class="col-xl-5 mb-4">
        <div class="card admin-card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle text-warning"></i> Sản phẩm sắp hết hàng
                </h5>
                <a href="<?php echo e(route('admin.products.index')); ?>?sort=stock" class="btn btn-sm btn-outline-warning">Xem tất cả</a>
            </div>
            <div class="card-body p-0">
                <?php if($lowStockProducts->count() > 0): ?>
                    <div class="list-group list-group-flush">
                        <?php $__currentLoopData = $lowStockProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?php echo e(Str::limit($product->name, 30)); ?></h6>
                                    <small class="text-muted"><?php echo e($product->sku); ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-danger rounded-pill">
                                        <?php echo e($product->quantity); ?> còn lại
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                        <p>Tất cả sản phẩm đều có đủ hàng</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Sales Chart
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('salesChart').getContext('2d');
        const monthlySales = <?php echo json_encode($monthlySales, 15, 512) ?>;
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: monthlySales.map(item => item.month),
                datasets: [{
                    label: 'Doanh số (₫)',
                    data: monthlySales.map(item => item.sales),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('vi-VN').format(value) + '₫';
                            }
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                elements: {
                    point: {
                        hoverRadius: 8
                    }
                }
            }
        });
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\BadmintonShop\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>