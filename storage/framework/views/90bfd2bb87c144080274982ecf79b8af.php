

<?php $__env->startSection('title', 'Giỏ hàng - Badminton Shop'); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-shopping-cart"></i> Giỏ hàng</h1>
                <a href="<?php echo e(route('products.index')); ?>" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Tiếp tục mua sắm
                </a>
            </div>

            <?php if($cartItems && count($cartItems) > 0): ?>
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Cart Items -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Sản phẩm trong giỏ (<?php echo e(count($cartItems)); ?> sản phẩm)</h5>
                            </div>
                            <div class="card-body p-0">
                                <?php $__currentLoopData = $cartItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="cart-item border-bottom" data-id="<?php echo e($item['id']); ?>">
                                        <div class="row align-items-center p-3">
                                            <div class="col-md-2 text-center">
                                                <img src="<?php echo e($item['product']->image ? asset('storage/' . $item['product']->image) : 'https://via.placeholder.com/100x100?text=No+Image'); ?>" 
                                                     class="img-fluid rounded" alt="<?php echo e($item['product']->name); ?>" style="width: 80px; height: 80px; object-fit: cover;">
                                            </div>
                                            <div class="col-md-3">
                                                <h6 class="mb-1"><?php echo e($item['product']->name); ?></h6>
                                                <small class="text-muted"><?php echo e($item['product']->category->name ?? 'N/A'); ?></small><br>
                                                <small class="text-muted">SKU: <?php echo e($item['product']->sku ?? 'N/A'); ?></small>
                                                <?php if($item['product']->quantity <= 0): ?>
                                                    <div class="mt-1">
                                                        <span class="badge bg-danger">Hết hàng</span>
                                                    </div>
                                                <?php elseif($item['product']->quantity < $item['quantity']): ?>
                                                    <div class="mt-1">
                                                        <span class="badge bg-warning">Chỉ còn <?php echo e($item['product']->quantity); ?> sản phẩm</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-2 text-center">
                                                <div class="price-info">
                                                    <?php if($item['product']->is_on_sale): ?>
                                                        <span class="text-danger fw-bold"><?php echo e(number_format($item['product']->sale_price)); ?>₫</span><br>
                                                        <small class="text-decoration-line-through text-muted"><?php echo e(number_format($item['product']->price)); ?>₫</small>
                                                    <?php else: ?>
                                                        <span class="fw-bold"><?php echo e(number_format($item['price'])); ?>₫</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="quantity-controls">
                                                    <div class="input-group input-group-sm">
                                                        <button class="btn btn-outline-secondary decrease-qty" type="button" data-id="<?php echo e($item['id']); ?>">-</button>
                                                        <input type="number" class="form-control text-center quantity-input" 
                                                               value="<?php echo e($item['quantity']); ?>" 
                                                               data-id="<?php echo e($item['id']); ?>" 
                                                               min="1" 
                                                               max="<?php echo e($item['product']->quantity); ?>">
                                                        <button class="btn btn-outline-secondary increase-qty" type="button" data-id="<?php echo e($item['id']); ?>">+</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2 text-center">
                                                <span class="fw-bold text-primary item-total"><?php echo e(number_format($item['subtotal'])); ?>₫</span>
                                            </div>
                                            <div class="col-md-1 text-center">
                                                <button class="btn btn-outline-danger btn-sm remove-from-cart" data-id="<?php echo e($item['id']); ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-md-6">
                                        <form action="<?php echo e(route('cart.clear')); ?>" method="POST" class="d-inline" onsubmit="return clearCartConfirm(event)">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="btn btn-outline-danger">
                                                <i class="fas fa-trash-alt"></i> Xóa tất cả
                                            </button>
                                        </form>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <button class="btn btn-outline-secondary" onclick="location.reload()">
                                            <i class="fas fa-sync-alt"></i> Cập nhật giỏ hàng
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Order Summary -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Tổng quan đơn hàng</h5>
                            </div>
                            <div class="card-body">
                                <div class="order-summary">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Tạm tính:</span>
                                        <span class="subtotal"><?php echo e(number_format($total)); ?>₫</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Phí vận chuyển:</span>
                                        <span class="shipping-fee">
                                            <?php if($total >= 500000): ?>
                                                <span class="text-success">Miễn phí</span>
                                            <?php else: ?>
                                                30,000₫
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <?php if($total >= 500000): ?>
                                        <small class="text-success mb-2 d-block">
                                            <i class="fas fa-check"></i> Bạn được miễn phí vận chuyển!
                                        </small>
                                    <?php else: ?>
                                        <small class="text-muted mb-2 d-block">
                                            <i class="fas fa-info-circle"></i> Mua thêm <?php echo e(number_format(500000 - $total)); ?>₫ để được miễn phí vận chuyển
                                        </small>
                                    <?php endif; ?>
                                    <hr>
                                    <div class="d-flex justify-content-between mb-3">
                                        <strong>Tổng cộng:</strong>
                                        <strong class="text-primary fs-5 final-total">
                                            <?php echo e(number_format($total >= 500000 ? $total : $total + 30000)); ?>₫
                                        </strong>
                                    </div>

                                    <!-- Coupon Code -->
                                    <div class="coupon-section mb-3">
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="Mã giảm giá" id="couponCode">
                                            <button class="btn btn-outline-primary" type="button" onclick="applyCoupon()">
                                                Áp dụng
                                            </button>
                                        </div>
                                        <div id="couponMessage" class="mt-2"></div>
                                    </div>

                                    <!-- Checkout Button -->
                                    <div class="d-grid gap-2">
                                        <?php if(auth()->guard()->guest()): ?>
                                            <div class="alert alert-info small">
                                                <i class="fas fa-info-circle"></i> 
                                                Vui lòng <a href="<?php echo e(route('login')); ?>" class="alert-link">đăng nhập</a> để tiến hành thanh toán.
                                            </div>
                                            <a href="<?php echo e(route('login')); ?>" class="btn btn-primary btn-lg">
                                                <i class="fas fa-sign-in-alt"></i> Đăng nhập để thanh toán
                                            </a>
                                        <?php else: ?>
                                            <a href="<?php echo e(route('checkout')); ?>" class="btn btn-success btn-lg" id="checkoutBtn">
                                                <i class="fas fa-credit-card"></i> Tiến hành thanh toán
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="<?php echo e(route('products.index')); ?>" class="btn btn-outline-primary">
                                            <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Security Badges -->
                        <div class="card mt-3">
                            <div class="card-body text-center">
                                <h6>Mua sắm an toàn</h6>
                                <div class="d-flex justify-content-center gap-3 mt-2">
                                    <i class="fas fa-shield-alt text-success fa-2x" title="Bảo mật SSL"></i>
                                    <i class="fas fa-lock text-primary fa-2x" title="Thanh toán an toàn"></i>
                                    <i class="fas fa-truck text-warning fa-2x" title="Giao hàng nhanh"></i>
                                </div>
                                <small class="text-muted mt-2 d-block">Thanh toán an toàn • Giao hàng nhanh chóng</small>
                            </div>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- Empty Cart -->
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-3x text-muted mb-4"></i>
                    <h3>Giỏ hàng của bạn đang trống</h3>
                    <p class="text-muted mb-4">Hãy thêm một số sản phẩm vào giỏ hàng để tiếp tục!</p>
                    <a href="<?php echo e(route('products.index')); ?>" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-bag"></i> Khám phá sản phẩm
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update quantity
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            updateCartItem(this.dataset.id, this.value);
        });
    });

    // Increase quantity
    document.querySelectorAll('.increase-qty').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.quantity-input');
            const newValue = parseInt(input.value) + 1;
            const maxValue = parseInt(input.max);
            
            if (newValue <= maxValue) {
                input.value = newValue;
                updateCartItem(this.dataset.id, newValue);
            }
        });
    });

    // Decrease quantity
    document.querySelectorAll('.decrease-qty').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.quantity-input');
            const newValue = parseInt(input.value) - 1;
            
            if (newValue >= 1) {
                input.value = newValue;
                updateCartItem(this.dataset.id, newValue);
            }
        });
    });

    // Remove from cart
    document.querySelectorAll('.remove-from-cart').forEach(btn => {
        btn.addEventListener('click', async function() {
            const confirmed = await showConfirm(
                'Xóa sản phẩm',
                'Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?',
                'Xóa',
                'fas fa-trash text-danger',
                'btn-danger'
            );
            
            if (confirmed) {
                removeFromCart(this.dataset.id);
            }
        });
    });
});

function updateCartItem(id, quantity) {
    console.log('Updating cart item:', id, 'quantity:', quantity);
    
    fetch(`/cart/update/${id}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({ quantity: quantity })
    })
    .then(response => {
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return response.json();
    })
    .then(data => {
        console.log('Update response data:', data);
        
        if (data.success) {
            // Reload page to update totals
            location.reload();
        } else {
            showToast('Lỗi', data.message || 'Có lỗi xảy ra khi cập nhật giỏ hàng', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Lỗi', 'Có lỗi xảy ra khi cập nhật giỏ hàng: ' + error.message, 'error');
    });
}

function removeFromCart(id) {
    console.log('Removing item from cart:', id);
    
    fetch(`/cart/remove/${id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('Remove response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return response.json();
    })
    .then(data => {
        console.log('Remove response data:', data);
        
        if (data.success) {
            showToast('Thành công', 'Đã xóa sản phẩm khỏi giỏ hàng', 'success');
            
            // Remove item from DOM
            const cartItem = document.querySelector(`.cart-item[data-id="${id}"]`);
            if (cartItem) {
                cartItem.remove();
            }
            
            // Check if cart is empty
            if (document.querySelectorAll('.cart-item').length === 0) {
                location.reload();
            } else {
                // Reload to update totals
                setTimeout(() => {
                    location.reload();
                }, 1000);
            }
        } else {
            showToast('Lỗi', data.message || 'Có lỗi xảy ra khi xóa sản phẩm', 'error');
        }
    })
    .catch(error => {
        console.error('Remove error details:', error);
        showToast('Lỗi', 'Có lỗi xảy ra khi xóa sản phẩm: ' + error.message, 'error');
    });
}

function applyCoupon() {
    const couponCode = document.getElementById('couponCode').value.trim();
    const messageDiv = document.getElementById('couponMessage');
    
    if (!couponCode) {
        messageDiv.innerHTML = '<small class="text-danger">Vui lòng nhập mã giảm giá</small>';
        return;
    }

    // Here you would typically make an AJAX request to validate and apply the coupon
    // For now, we'll show a placeholder message
    messageDiv.innerHTML = '<small class="text-info">Chức năng mã giảm giá sẽ sớm được cập nhật</small>';
}

// Check cart validity before checkout
document.addEventListener('DOMContentLoaded', function() {
    const checkoutBtn = document.getElementById('checkoutBtn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function(e) {
            const outOfStockItems = document.querySelectorAll('.badge-danger');
            if (outOfStockItems.length > 0) {
                e.preventDefault();
                showToast('Cảnh báo', 'Vui lòng xóa các sản phẩm hết hàng trước khi thanh toán', 'warning');
            }
        });
    }
});

// Clear cart confirmation
async function clearCartConfirm(event) {
    event.preventDefault();
    
    const confirmed = await showConfirm(
        'Xóa tất cả sản phẩm',
        'Bạn có chắc muốn xóa tất cả sản phẩm trong giỏ hàng?',
        'Xóa tất cả',
        'fas fa-trash text-danger',
        'btn-danger'
    );
    
    if (confirmed) {
        event.target.submit();
    }
    
    return false;
}
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.cart-item {
    transition: all 0.3s ease;
}

.cart-item:hover {
    background-color: #f8f9fa;
}

.quantity-controls input {
    max-width: 80px;
}

.price-info {
    font-size: 0.9rem;
}

.order-summary {
    font-size: 0.95rem;
}

.security-badge {
    opacity: 0.8;
    transition: opacity 0.3s ease;
}

.security-badge:hover {
    opacity: 1;
}
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\xampp\htdocs\BadmintonShop\resources\views/cart/index.blade.php ENDPATH**/ ?>