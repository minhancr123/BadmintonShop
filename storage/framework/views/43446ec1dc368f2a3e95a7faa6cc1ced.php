

<?php $__env->startSection('title', 'Quản lý khách hàng'); ?>
<?php $__env->startSection('page-title', 'Quản lý khách hàng'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Dashboard</a></li>
    <li class="breadcrumb-item active">Khách hàng</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<!-- Filters -->
<div class="card admin-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Tìm kiếm</label>
                <input type="text" class="form-control" name="search" value="<?php echo e(request('search')); ?>" 
                       placeholder="Tên, email, SĐT...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Vai trò</label>
                <select class="form-select" name="role">
                    <option value="">Tất cả</option>
                    <option value="admin" <?php if(request('role') == 'admin'): echo 'selected'; endif; ?>>Quản trị viên</option>
                    <option value="user" <?php if(request('role') == 'user'): echo 'selected'; endif; ?>>Khách hàng</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Trạng thái</label>
                <select class="form-select" name="status">
                    <option value="">Tất cả</option>
                    <option value="active" <?php if(request('status') == 'active'): echo 'selected'; endif; ?>>Đang hoạt động</option>
                    <option value="blocked" <?php if(request('status') == 'blocked'): echo 'selected'; endif; ?>>Đã khóa</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search"></i> Lọc
                </button>
                <a href="<?php echo e(route('admin.users.index')); ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Xóa lọc
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card admin-card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-list"></i> Danh sách khách hàng (<?php echo e($users->total()); ?> khách hàng)
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if($users->count() > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0 table-admin">
                    <thead class="table-light">
                        <tr>
                            <th>Khách hàng</th>
                            <th>Email</th>
                            <th>Điện thoại</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th>Đơn hàng</th>
                            <th>Tổng chi tiêu</th>
                            <th>Ngày đăng ký</th>
                            <th width="150">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-3">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0"><?php echo e($user->name); ?></h6>
                                            <?php if($user->orders->isNotEmpty()): ?>
                                                <small class="text-muted">
                                                    Mua gần nhất: <?php echo e($user->orders->first()->created_at->diffForHumans()); ?>

                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-info"><?php echo e($user->email); ?></span>
                                    <?php if($user->email_verified_at): ?>
                                        <br><small class="text-success">
                                            <i class="fas fa-check-circle"></i> Đã xác thực
                                        </small>
                                    <?php else: ?>
                                        <br><small class="text-warning">
                                            <i class="fas fa-exclamation-circle"></i> Chưa xác thực
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($user->phone): ?>
                                        <span class="text-muted"><?php echo e($user->phone); ?></span>
                                    <?php else: ?>
                                        <small class="text-muted">Chưa cập nhật</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($user->isAdmin()): ?>
                                        <span class="badge bg-danger">Quản trị viên</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary">Khách hàng</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($user->is_blocked): ?>
                                        <span class="badge bg-danger">
                                            <i class="fas fa-ban"></i> Đã khóa
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle"></i> Hoạt động
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="text-center">
                                        <span class="fw-bold text-primary"><?php echo e($user->orders->count()); ?></span>
                                        <br><small class="text-muted">đơn hàng</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-center">
                                        <?php
                                            $totalSpent = $user->orders()->where('payment_status', 'paid')->sum('total_amount');
                                        ?>
                                        <span class="fw-bold text-success"><?php echo e(number_format($totalSpent)); ?>₫</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold"><?php echo e($user->created_at->format('d/m/Y')); ?></span>
                                    <br><small class="text-muted"><?php echo e($user->created_at->diffForHumans()); ?></small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="<?php echo e(route('admin.users.show', $user)); ?>" 
                                           class="btn btn-outline-primary" title="Chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if($user->id !== auth()->id()): ?>
                                            <!-- Dropdown for role change -->
                                            <div class="dropdown">
                                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" 
                                                        data-bs-toggle="dropdown" title="Thay đổi vai trò">
                                                    <i class="fas fa-user-cog"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <?php if(!$user->isAdmin()): ?>
                                                        <li><a class="dropdown-item role-change-btn" href="#" 
                                                               data-user-id="<?php echo e($user->id); ?>" data-role="admin">
                                                            <i class="fas fa-crown text-danger"></i> Thăng cấp Admin</a></li>
                                                    <?php else: ?>
                                                        <li><a class="dropdown-item role-change-btn" href="#" 
                                                               data-user-id="<?php echo e($user->id); ?>" data-role="user">
                                                            <i class="fas fa-user text-primary"></i> Hạ cấp User</a></li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                            
                                            <!-- Block/Unblock button -->
                                            <?php if($user->is_blocked): ?>
                                                <button type="button" class="btn btn-outline-success unblock-btn" 
                                                        data-user-id="<?php echo e($user->id); ?>" 
                                                        data-user-name="<?php echo e($user->name); ?>"
                                                        title="Mở khóa tài khoản">
                                                    <i class="fas fa-unlock"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-outline-danger block-btn" 
                                                        data-user-id="<?php echo e($user->id); ?>" 
                                                        data-user-name="<?php echo e($user->name); ?>"
                                                        title="Khóa tài khoản">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <!-- Reset password button -->
                                            <button type="button" class="btn btn-outline-warning reset-password-btn" 
                                                    data-user-id="<?php echo e($user->id); ?>" 
                                                    data-user-name="<?php echo e($user->name); ?>"
                                                    title="Đặt lại mật khẩu">
                                                <i class="fas fa-key"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5>Không tìm thấy khách hàng nào</h5>
                <p class="text-muted">Hãy thử thay đổi điều kiện lọc</p>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if($users->hasPages()): ?>
        <div class="card-footer bg-white">
            <?php echo e($users->appends(request()->query())->links()); ?>

        </div>
    <?php endif; ?>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .avatar-circle {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Debug: Check if required functions exist
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded');
        console.log('showConfirm function exists:', typeof window.showConfirm);
        console.log('showToast function exists:', typeof window.showToast);
        console.log('Bootstrap exists:', typeof bootstrap);
        
        // Add event listeners for role change buttons
        document.querySelectorAll('.role-change-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const userId = this.getAttribute('data-user-id');
                const role = this.getAttribute('data-role');
                console.log('Role change button clicked:', userId, role);
                updateRole(userId, role);
            });
        });
        
        // Add event listeners for block buttons
        document.querySelectorAll('.block-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const userId = this.getAttribute('data-user-id');
                const userName = this.getAttribute('data-user-name');
                blockUser(userId, userName);
            });
        });
        
        // Add event listeners for unblock buttons
        document.querySelectorAll('.unblock-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const userId = this.getAttribute('data-user-id');
                const userName = this.getAttribute('data-user-name');
                unblockUser(userId, userName);
            });
        });
        
        // Add event listeners for reset password buttons
        document.querySelectorAll('.reset-password-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const userId = this.getAttribute('data-user-id');
                const userName = this.getAttribute('data-user-name');
                resetPassword(userId, userName);
            });
        });
    });

    async function updateRole(userId, role) {
        console.log('updateRole called with:', userId, role);
        
        // Check if functions exist
        if (typeof window.showConfirm !== 'function') {
            alert('Lỗi: Function showConfirm không tồn tại');
            return;
        }
        
        const roleNames = {
            'admin': 'Quản trị viên',
            'user': 'Khách hàng'
        };
        
        try {
            const confirmed = await showConfirm(
                'Thay đổi vai trò',
                `Bạn có chắc muốn thay đổi vai trò người dùng thành "${roleNames[role]}"?`,
                'Thay đổi',
                'fas fa-user-cog text-primary',
                'btn-primary'
            );
            console.log('Confirmation result:', confirmed);
        
        if (confirmed) {
            const url = `<?php echo e(route('admin.users.update-role', '__USER_ID__')); ?>`.replace('__USER_ID__', userId);
            console.log('Making PATCH request to:', url);
            
            fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ role: role })
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}, statusText: ${response.statusText}`);
                }
                
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    if (typeof window.showToast === 'function') {
                        showToast('Thành công', 'Vai trò người dùng đã được cập nhật!', 'success');
                    } else {
                        alert('Vai trò người dùng đã được cập nhật!');
                    }
                    setTimeout(() => location.reload(), 1000);
                } else {
                    if (typeof window.showToast === 'function') {
                        showToast('Lỗi', data.message || 'Có lỗi xảy ra khi cập nhật vai trò', 'error');
                    } else {
                        alert('Lỗi: ' + (data.message || 'Có lỗi xảy ra khi cập nhật vai trò'));
                    }
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                if (typeof window.showToast === 'function') {
                    showToast('Lỗi', 'Có lỗi xảy ra khi cập nhật vai trò: ' + error.message, 'error');
                } else {
                    alert('Lỗi: Có lỗi xảy ra khi cập nhật vai trò: ' + error.message);
                }
            });
        }
        } catch (error) {
            console.error('Error in updateRole:', error);
            alert('Lỗi: ' + error.message);
        }
    }
    
    // Block user function
    async function blockUser(userId, userName) {
        try {
            const confirmed = await showConfirm(
                'Khóa tài khoản',
                `Bạn có chắc muốn khóa tài khoản của "${userName}"? Người dùng sẽ không thể đăng nhập.`,
                'Khóa tài khoản',
                'fas fa-ban text-danger',
                'btn-danger'
            );
            
            if (confirmed) {
                const url = `<?php echo e(route('admin.users.block', '__USER_ID__')); ?>`.replace('__USER_ID__', userId);
                
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    showToast('Thành công', 'Đã khóa tài khoản người dùng!', 'success');
                    setTimeout(() => location.reload(), 1000);
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Lỗi', 'Có lỗi xảy ra khi khóa tài khoản', 'error');
                });
            }
        } catch (error) {
            console.error('Error in blockUser:', error);
        }
    }
    
    // Unblock user function
    async function unblockUser(userId, userName) {
        try {
            const confirmed = await showConfirm(
                'Mở khóa tài khoản',
                `Bạn có chắc muốn mở khóa tài khoản của "${userName}"?`,
                'Mở khóa',
                'fas fa-unlock text-success',
                'btn-success'
            );
            
            if (confirmed) {
                const url = `<?php echo e(route('admin.users.unblock', '__USER_ID__')); ?>`.replace('__USER_ID__', userId);
                
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    showToast('Thành công', 'Đã mở khóa tài khoản người dùng!', 'success');
                    setTimeout(() => location.reload(), 1000);
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Lỗi', 'Có lỗi xảy ra khi mở khóa tài khoản', 'error');
                });
            }
        } catch (error) {
            console.error('Error in unblockUser:', error);
        }
    }
    
    // Reset password function
    async function resetPassword(userId, userName) {
        try {
            const confirmed = await showConfirm(
                'Đặt lại mật khẩu',
                `Bạn có chắc muốn đặt lại mật khẩu cho "${userName}"? Mật khẩu mới sẽ là: <strong>123</strong>`,
                'Đặt lại',
                'fas fa-key text-warning',
                'btn-warning'
            );
            
            if (confirmed) {
                const url = `<?php echo e(route('admin.users.reset-password', '__USER_ID__')); ?>`.replace('__USER_ID__', userId);
                
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    showToast('Thành công', 'Mật khẩu đã được đặt lại thành: 123', 'success');
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Lỗi', 'Có lỗi xảy ra khi đặt lại mật khẩu', 'error');
                });
            }
        } catch (error) {
            console.error('Error in resetPassword:', error);
        }
    }
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\GIGABYTE\Desktop\BadmintonShop\resources\views/admin/users/index.blade.php ENDPATH**/ ?>