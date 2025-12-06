

<?php $__env->startSection('title', 'Chi tiết người dùng'); ?>
<?php $__env->startSection('page-title', 'Chi tiết người dùng'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.users.index')); ?>">Người dùng</a></li>
    <li class="breadcrumb-item active">Chi tiết</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <!-- Thông tin cá nhân -->
    <div class="col-md-4">
        <div class="card admin-card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-user"></i> Thông tin cá nhân</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="avatar-lg mx-auto mb-3">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-user fa-2x text-white"></i>
                        </div>
                    </div>
                    <h5 class="mb-1"><?php echo e($user->name); ?></h5>
                    <p class="text-muted mb-0"><?php echo e($user->email); ?></p>
                </div>

                <div class="table-responsive">
                    <table class="table table-borderless table-sm">
                        <tr><td class="fw-bold">ID:</td><td><?php echo e($user->id); ?></td></tr>
                        <tr><td class="fw-bold">Họ tên:</td><td><?php echo e($user->name); ?></td></tr>
                        <tr><td class="fw-bold">Email:</td><td><?php echo e($user->email); ?></td></tr>
                        <tr><td class="fw-bold">Số điện thoại:</td><td><?php echo e($user->phone ?? 'Chưa cập nhật'); ?></td></tr>
                        <tr><td class="fw-bold">Địa chỉ:</td><td><?php echo e($user->address ?? 'Chưa cập nhật'); ?></td></tr>
                        <tr><td class="fw-bold">Ngày đăng ký:</td><td><?php echo e($user->created_at->format('d/m/Y H:i')); ?></td></tr>
                        <tr>
                            <td class="fw-bold">Xác thực email:</td>
                            <td>
                                <?php if($user->email_verified_at): ?>
                                    <span class="badge bg-success">Đã xác thực</span><br>
                                    <small class="text-muted"><?php echo e($user->email_verified_at->format('d/m/Y H:i')); ?></small>
                                <?php else: ?>
                                    <span class="badge bg-warning">Chưa xác thực</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr><td class="fw-bold">Lần cuối online:</td><td><?php echo e($user->updated_at->diffForHumans()); ?></td></tr>
                        <tr>
                            <td class="fw-bold">Trạng thái:</td>
                            <td>
                                <?php if($user->is_blocked): ?>
                                    <span class="badge bg-danger">Đã bị khóa</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Đang hoạt động</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Thống kê + Đơn hàng -->
    <div class="col-md-8">
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card admin-card bg-primary text-white">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h3><?php echo e($userStats['total_orders']); ?></h3>
                            <p class="mb-0">Tổng đơn hàng</p>
                        </div>
                        <i class="fas fa-shopping-cart fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card admin-card bg-success text-white">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h3><?php echo e(number_format($userStats['total_spent'])); ?>₫</h3>
                            <p class="mb-0">Tổng chi tiêu</p>
                        </div>
                        <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card admin-card bg-info text-white">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h3>
                                <?php if($userStats['last_order']): ?>
                                    <?php echo e($userStats['last_order']->created_at->diffForHumans()); ?>

                                <?php else: ?>
                                    Chưa có
                                <?php endif; ?>
                            </h3>
                            <p class="mb-0">Đơn hàng cuối</p>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card admin-card">
            <div class="card-header bg-white d-flex justify-content-between">
                <h5 class="mb-0"><i class="fas fa-history"></i> Lịch sử đơn hàng</h5>
                <a href="<?php echo e(route('admin.orders.index', ['search' => $user->email])); ?>" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-external-link-alt"></i> Xem tất cả
                </a>
            </div>
            <div class="card-body p-0">
                <?php if($user->orders->count()): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã đơn</th><th>Ngày</th><th>Sản phẩm</th><th>Tổng</th>
                                    <th>Trạng thái</th><th>Thanh toán</th><th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $user->orders->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><a href="<?php echo e(route('admin.orders.show', $order)); ?>" class="fw-bold"><?php echo e($order->order_number); ?></a></td>
                                        <td><?php echo e($order->created_at->format('d/m/Y H:i')); ?></td>
                                        <td><?php echo e($order->orderItems->count()); ?></td>
                                        <td class="text-success fw-bold"><?php echo e(number_format($order->total_amount)); ?>₫</td>
                                        <td><span class="badge bg-<?php echo e($order->status === 'cancelled' ? 'danger' : 'primary'); ?>"><?php echo e($order->status); ?></span></td>
                                        <td><span class="badge bg-<?php echo e($order->payment_status === 'paid' ? 'success' : 'warning'); ?>"><?php echo e($order->payment_status); ?></span></td>
                                        <td>
                                            <a href="<?php echo e(route('admin.orders.show', $order)); ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">Chưa có đơn hàng nào</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Action buttons -->
<div class="row mt-4">
    <div class="col-12 d-flex justify-content-between">
        <a href="<?php echo e(route('admin.users.index')); ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại danh sách
        </a>

        <div class="d-flex gap-2">
            <!-- Reset mật khẩu -->
            <form action="<?php echo e(route('admin.users.reset-password', $user->id)); ?>" method="POST" class="d-flex gap-2">
                <?php echo csrf_field(); ?>
                <button class="btn btn-outline-warning btn-sm"><i class="fas fa-key"></i> Đặt lại mật khẩu</button>
            </form>

            <!-- Block/Unblock -->
            <form action="<?php echo e($user->is_blocked ? route('admin.users.unblock', $user->id) : route('admin.users.block', $user->id)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-outline-<?php echo e($user->is_blocked ? 'success' : 'danger'); ?> btn-sm">
                    <i class="fas fa-user-lock"></i> <?php echo e($user->is_blocked ? 'Mở khóa' : 'Tạm khóa'); ?>

                </button>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\BadmintonShop\resources\views/admin/users/show.blade.php ENDPATH**/ ?>