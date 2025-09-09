

<?php $__env->startSection('title', 'Tài khoản của tôi - Badminton Shop'); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1>Xin chào, <?php echo e(Auth::user()->name); ?>!</h1>
                    <p class="text-muted">Quản lý tài khoản và theo dõi đơn hàng của bạn</p>
                </div>
                <div>
                    <small class="text-muted">Đăng nhập lần cuối: <?php echo e(Auth::user()->updated_at->diffForHumans()); ?></small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- User Info -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Thông tin tài khoản</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar-circle bg-primary text-white d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 80px; height: 80px; font-size: 2rem;">
                            <?php echo e(strtoupper(substr(Auth::user()->name, 0, 1))); ?>

                        </div>
                    </div>
                    <div class="user-info">
                        <p class="mb-2"><strong>Tên:</strong> <?php echo e(Auth::user()->name); ?></p>
                        <p class="mb-2"><strong>Email:</strong> <?php echo e(Auth::user()->email); ?></p>
                        <p class="mb-2"><strong>Điện thoại:</strong> <?php echo e(Auth::user()->phone ?? 'Chưa cập nhật'); ?></p>
                        <p class="mb-2"><strong>Địa chỉ:</strong> <?php echo e(Auth::user()->address ?? 'Chưa cập nhật'); ?></p>
                        <p class="mb-0">
                            <strong>Trạng thái:</strong> 
                            <span class="badge bg-success"><?php echo e(ucfirst(Auth::user()->role)); ?></span>
                        </p>
                    </div>
                    <div class="mt-3">
                        <a href="#" class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-edit"></i> Chỉnh sửa thông tin
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Thao tác nhanh</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-xl-3 mb-3">
                            <a href="<?php echo e(route('products.index')); ?>" class="btn btn-outline-primary w-100 p-3 text-decoration-none">
                                <i class="fas fa-shopping-bag fa-2x mb-2"></i>
                                <div>Mua sắm</div>
                            </a>
                        </div>
                        <div class="col-md-6 col-xl-3 mb-3">
                            <a href="<?php echo e(route('cart.index')); ?>" class="btn btn-outline-success w-100 p-3 text-decoration-none">
                                <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                                <div>Giỏ hàng</div>
                            </a>
                        </div>
                        <div class="col-md-6 col-xl-3 mb-3">
                            <a href="<?php echo e(route('orders.index')); ?>" class="btn btn-outline-info w-100 p-3 text-decoration-none">
                                <i class="fas fa-box fa-2x mb-2"></i>
                                <div>Đơn hàng</div>
                            </a>
                        </div>
                        <div class="col-md-6 col-xl-3 mb-3">
                            <a href="<?php echo e(route('contact')); ?>" class="btn btn-outline-warning w-100 p-3 text-decoration-none">
                                <i class="fas fa-headset fa-2x mb-2"></i>
                                <div>Hỗ trợ</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <?php if(isset($orders) && $orders->count() > 0): ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Đơn hàng gần đây</h5>
                    <a href="<?php echo e(route('orders.index')); ?>" class="btn btn-outline-primary btn-sm">Xem tất cả</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã đơn hàng</th>
                                    <th>Ngày đặt</th>
                                    <th>Trạng thái</th>
                                    <th>Tổng tiền</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo e($order->order_number ?? $order->id); ?></strong>
                                    </td>
                                    <td><?php echo e($order->created_at->format('d/m/Y H:i')); ?></td>
                                    <td>
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
                                    </td>
                                    <td>
                                        <strong><?php echo e(number_format($order->total_amount)); ?>₫</strong>
                                    </td>
                                    <td>
                                        <a href="<?php echo e(route('orders.show', $order)); ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye"></i> Xem
                                        </a>
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
    <?php else: ?>
    <!-- No Orders -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                    <h5>Chưa có đơn hàng nào</h5>
                    <p class="text-muted mb-4">Bạn chưa có đơn hàng nào. Hãy bắt đầu mua sắm ngay!</p>
                    <a href="<?php echo e(route('products.index')); ?>" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-cart"></i> Bắt đầu mua sắm
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Statistics Cards (if user is admin) -->
    <?php if(Auth::user()->role === 'admin'): ?>
    <div class="row mt-4">
        <div class="col-12 mb-3">
            <h4>Quản trị viên</h4>
            <div class="d-flex gap-2">
                <a href="<?php echo e(route('admin.dashboard')); ?>" class="btn btn-danger">
                    <i class="fas fa-cog"></i> Admin Panel
                </a>
                <a href="<?php echo e(route('admin.products.index')); ?>" class="btn btn-outline-danger">
                    <i class="fas fa-box"></i> Quản lý sản phẩm
                </a>
                <a href="<?php echo e(route('admin.orders.index')); ?>" class="btn btn-outline-danger">
                    <i class="fas fa-clipboard-list"></i> Quản lý đơn hàng
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Account Security -->
    <div class="row mt-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Bảo mật tài khoản</h5>
                </div>
                <div class="card-body">
                    <div class="security-items">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <div>
                                <strong>Mật khẩu</strong>
                                <div class="text-muted small">Cập nhật lần cuối: <?php echo e(Auth::user()->updated_at->diffForHumans()); ?></div>
                            </div>
                            <a href="#" class="btn btn-outline-primary btn-sm">Đổi mật khẩu</a>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Xác thực email</strong>
                                <div class="text-muted small">
                                    <?php if(Auth::user()->email_verified_at): ?>
                                        <span class="text-success">Đã xác thực</span>
                                    <?php else: ?>
                                        <span class="text-warning">Chưa xác thực</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if(!Auth::user()->email_verified_at): ?>
                                <a href="#" class="btn btn-outline-warning btn-sm">Xác thực ngay</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Hoạt động gần đây</h5>
                </div>
                <div class="card-body">
                    <div class="activity-list">
                        <div class="activity-item mb-3 pb-2 border-bottom">
                            <i class="fas fa-sign-in-alt text-success me-2"></i>
                            <span>Đăng nhập vào <?php echo e(Auth::user()->updated_at->format('d/m/Y H:i')); ?></span>
                        </div>
                        <div class="activity-item mb-3 pb-2 border-bottom">
                            <i class="fas fa-user-plus text-info me-2"></i>
                            <span>Tạo tài khoản vào <?php echo e(Auth::user()->created_at->format('d/m/Y H:i')); ?></span>
                        </div>
                        <?php if(isset($orders) && $orders->count() > 0): ?>
                        <div class="activity-item">
                            <i class="fas fa-shopping-cart text-primary me-2"></i>
                            <span>Đặt hàng lần cuối: <?php echo e($orders->first()->created_at->diffForHumans()); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
.avatar-circle {
    font-weight: bold;
}

.btn.text-decoration-none:hover {
    text-decoration: none !important;
}

.activity-item {
    font-size: 0.9rem;
}

.security-items .border-bottom:last-child {
    border-bottom: none !important;
}
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\xampp\htdocs\BadmintonShop\resources\views/dashboard.blade.php ENDPATH**/ ?>