

<?php $__env->startSection('title', 'Thanh toán - Badminton Shop'); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="mb-4">
                <h1><i class="fas fa-credit-card"></i> Thanh toán</h1>
                <p class="text-muted">Hoàn tất đơn hàng của bạn</p>
            </div>

            <!-- Progress Steps -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="progress-steps d-flex justify-content-center">
                        <div class="step completed">
                            <div class="step-icon"><i class="fas fa-shopping-cart"></i></div>
                            <div class="step-text">Giỏ hàng</div>
                        </div>
                        <div class="step-line completed"></div>
                        <div class="step active">
                            <div class="step-icon"><i class="fas fa-credit-card"></i></div>
                            <div class="step-text">Thanh toán</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step">
                            <div class="step-icon"><i class="fas fa-check"></i></div>
                            <div class="step-text">Hoàn tất</div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if($cartItems && count($cartItems) > 0): ?>
            <form method="POST" action="<?php echo e(route('orders.store')); ?>" id="checkoutForm">
                <?php echo csrf_field(); ?>
                <div class="row">
                    <!-- Checkout Form -->
                    <div class="col-lg-8">
                        <!-- Shipping Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-shipping-fast"></i> Thông tin giao hàng
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="shipping_name" class="form-label">Họ và tên người nhận <span
                                                class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control <?php $__errorArgs = ['shipping_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                            id="shipping_name" name="shipping_name"
                                            value="<?php echo e(old('shipping_name', auth()->user()->name)); ?>" required
                                            placeholder="Nhập họ và tên">
                                        <?php $__errorArgs = ['shipping_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="shipping_phone" class="form-label">Số điện thoại <span
                                                class="text-danger">*</span></label>
                                        <input type="tel"
                                            class="form-control <?php $__errorArgs = ['shipping_phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                            id="shipping_phone" name="shipping_phone"
                                            value="<?php echo e(old('shipping_phone', auth()->user()->phone)); ?>" required
                                            placeholder="Nhập số điện thoại">
                                        <?php $__errorArgs = ['shipping_phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="shipping_address" class="form-label">Địa chỉ giao hàng <span
                                                class="text-danger">*</span></label>
                                        <textarea class="form-control <?php $__errorArgs = ['shipping_address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                            id="shipping_address" name="shipping_address" rows="3" required
                                            placeholder="Nhập địa chỉ chi tiết (số nhà, đường, quận/huyện, tỉnh/thành)"><?php echo e(old('shipping_address', auth()->user()->address)); ?></textarea>
                                        <?php $__errorArgs = ['shipping_address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="col-12">
                                        <label for="notes" class="form-label">Ghi chú đơn hàng</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="2"
                                            placeholder="Ghi chú về đơn hàng, thời gian giao hàng..."><?php echo e(old('notes')); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-money-bill-wave"></i> Phương thức thanh toán
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="payment-methods">
                                    <div class="form-check payment-option mb-3 p-3 border rounded">
                                        <input class="form-check-input" type="radio" name="payment_method" id="cod"
                                            value="cod" checked>
                                        <label class="form-check-label w-100" for="cod">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-hand-holding-usd fa-2x text-success me-3"></i>
                                                <div>
                                                    <h6 class="mb-1">Thanh toán khi nhận hàng (COD)</h6>
                                                    <small class="text-muted">Thanh toán bằng tiền mặt khi nhận được
                                                        hàng</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="form-check payment-option mb-3 p-3 border rounded">
                                        <input class="form-check-input" type="radio" name="payment_method"
                                            id="bank_transfer" value="bank_transfer">
                                        <label class="form-check-label w-100" for="bank_transfer">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-university fa-2x text-primary me-3"></i>
                                                <div>
                                                    <h6 class="mb-1">Chuyển khoản ngân hàng</h6>
                                                    <small class="text-muted">Chuyển khoản trước, giao hàng sau xác
                                                        nhận</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                    <!-- Credit/Debit Card group -->
                                    <div class="form-check payment-option mb-3 p-3 border rounded">
                                        <input class="form-check-input" type="radio" name="payment_method"
                                            id="credit_card" value="credit_card">
                                        <label class="form-check-label" for="credit_card">
                                            <h6 class="mb-1">Thẻ tín dụng / ghi nợ</h6>
                                        </label>

                                        <!-- Cổng thanh toán con -->
                                        <div id="credit-sub-options" class="mt-2 ms-4 d-none">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="sub_payment"
                                                    id="momo" value="momo">
                                                <label class="form-check-label" for="momo">
                                                    <img src="https://developers.momo.vn/v3/vi/assets/images/square-8c08a00f550e40a2efafea4a005b1232.png"
                                                        style="width:30px"> MoMo
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="sub_payment"
                                                    id="vnpay" value="vnpay">
                                                <label class="form-check-label" for="vnpay">
                                                    <img src="https://vnpay.vn/favicon.ico" style="width:30px"> VNPay
                                                </label>
                                            </div>

                                        </div>
                                    </div>


                                </div>

                                <!-- Bank Transfer Details -->
                                <div id="bankTransferDetails" class="alert alert-info" style="display: none;">
                                    <h6><i class="fas fa-info-circle"></i> Thông tin chuyển khoản:</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Ngân hàng:</strong> Vietcombank</p>
                                            <p class="mb-1"><strong>Số tài khoản:</strong> 0123456789</p>
                                            <p class="mb-1"><strong>Tên tài khoản:</strong> BADMINTON SHOP</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Nội dung CK:</strong> [Họ tên] - [Số điện thoại]</p>
                                            <p class="mb-0"><small class="text-muted">Đơn hàng sẽ được xử lý sau khi
                                                    chúng tôi nhận được thanh toán</small></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="form-check">
                                    <input class="form-check-input <?php $__errorArgs = ['terms'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" type="checkbox"
                                        id="terms" name="terms" required>
                                    <label class="form-check-label" for="terms">
                                        Tôi đã đọc và đồng ý với
                                        <a href="<?php echo e(route('terms')); ?>" target="_blank">Điều khoản sử dụng</a> và
                                        <a href="<?php echo e(route('privacy')); ?>" target="_blank">Chính sách bảo mật</a>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <?php $__errorArgs = ['terms'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="col-lg-4">
                        <div class="card sticky-top" style="top: 20px;">
                            <div class="card-header">
                                <h5 class="mb-0">Tóm tắt đơn hàng</h5>
                            </div>
                            <div class="card-body">
                                <!-- Cart Items -->
                                <div class="order-items mb-3" style="max-height: 300px; overflow-y: auto;">
                                    <?php $__currentLoopData = $cartItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                        <img src="<?php echo e($item['product']->image ? asset('storage/' . $item['product']->image) : 'https://via.placeholder.com/60x60?text=No+Image'); ?>"
                                            class="rounded me-3" alt="<?php echo e($item['product']->name); ?>"
                                            style="width: 60px; height: 60px; object-fit: cover;">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?php echo e($item['product']->name); ?></h6>
                                            <small class="text-muted">
                                                <?php echo e($item['quantity']); ?> x <?php echo e(number_format($item['price'])); ?>₫
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <strong><?php echo e(number_format($item['subtotal'])); ?>₫</strong>
                                        </div>
                                    </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>

                                <!-- Order Totals -->
                                <div class="order-totals">
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
                                        <i class="fas fa-check"></i> Miễn phí vận chuyển!
                                    </small>
                                    <?php else: ?>
                                    <small class="text-muted mb-2 d-block">
                                        <i class="fas fa-info-circle"></i> Mua thêm
                                        <?php echo e(number_format(500000 - $total)); ?>₫ để được miễn phí vận chuyển
                                    </small>
                                    <?php endif; ?>
                                    <hr>
                                    <div class="d-flex justify-content-between mb-3">
                                        <strong class="fs-5">Tổng cộng:</strong>
                                        <strong class="text-primary fs-4">
                                            <?php echo e(number_format($total >= 500000 ? $total : $total + 30000)); ?>₫
                                        </strong>
                                    </div>
                                </div>

                                <!-- Place Order Button -->
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-success btn-lg" id="placeOrderBtn">
                                        <i class="fas fa-check-circle"></i> Đặt hàng
                                    </button>
                                    <a href="<?php echo e(route('cart.index')); ?>" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left"></i> Quay lại giỏ hàng
                                    </a>
                                </div>

                                <!-- Security Badges -->
                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-shield-alt text-success"></i> Thanh toán an toàn
                                        <i class="fas fa-lock text-primary ms-2"></i> Mã hóa SSL
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <?php else: ?>
            <!-- Empty Cart -->
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart fa-3x text-muted mb-4"></i>
                <h4>Giỏ hàng trống</h4>
                <p class="text-muted mb-4">Bạn không có sản phẩm nào trong giỏ hàng để thanh toán.</p>
                <a href="<?php echo e(route('products.index')); ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
.progress-steps {
    margin-bottom: 2rem;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
}

.step-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    margin-bottom: 0.5rem;
    transition: all 0.3s ease;
}

.step.completed .step-icon {
    background: #28a745;
    color: white;
}

.step.active .step-icon {
    background: #007bff;
    color: white;
}

.step-text {
    font-size: 0.875rem;
    color: #6c757d;
    text-align: center;
}

.step.completed .step-text,
.step.active .step-text {
    color: #333;
    font-weight: 500;
}

.step-line {
    width: 100px;
    height: 2px;
    background: #e9ecef;
    margin: 25px 0;
    transition: all 0.3s ease;
}

.step-line.completed {
    background: #28a745;
}

.payment-option {
    cursor: pointer;
    transition: all 0.3s ease;
}

.payment-option:hover {
    background-color: #f8f9fa;
}

.payment-option input[type="radio"]:checked+label {
    background-color: #e3f2fd;
    border-color: #2196f3;
}

.order-items {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    background: #f8f9fa;
}

@media (max-width: 768px) {
    .step-line {
        width: 50px;
    }

    .progress-steps {
        transform: scale(0.8);
    }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle bank transfer details
    const bankTransferRadio = document.getElementById('bank_transfer');
    const bankTransferDetails = document.getElementById('bankTransferDetails');
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');

    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            if (this.value === 'bank_transfer') {
                bankTransferDetails.style.display = 'block';
            } else {
                bankTransferDetails.style.display = 'none';
            }
        });
    });

    // Form validation and submission
    const checkoutForm = document.getElementById('checkoutForm');
    const placeOrderBtn = document.getElementById('placeOrderBtn');

    checkoutForm.addEventListener('submit', function(e) {
        // Disable the button to prevent double submission
        placeOrderBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
        placeOrderBtn.disabled = true;

        // Re-enable button after 10 seconds as a fallback
        setTimeout(() => {
            placeOrderBtn.innerHTML = '<i class="fas fa-check-circle"></i> Đặt hàng';
            placeOrderBtn.disabled = false;
        }, 10000);
    });

    // Auto-fill user data from profile
    const useProfileBtn = document.getElementById('useProfile');
    if (useProfileBtn) {
        useProfileBtn.addEventListener('click', function() {
            // This would populate form with user's saved data
            console.log('Using profile data');
        });
    }

    // Validate phone number format (Vietnamese format)
    const phoneInput = document.getElementById('shipping_phone');
    phoneInput.addEventListener('input', function() {
        const phonePattern = /^(0|\+84)[3-9][0-9]{8}$/;
        const isValid = phonePattern.test(this.value.replace(/\s/g, ''));

        if (this.value && !isValid) {
            this.setCustomValidity(
                'Vui lòng nhập số điện thoại hợp lệ (10-11 số, bắt đầu bằng 0 hoặc +84)');
        } else {
            this.setCustomValidity('');
        }
    });
});

document.addEventListener("DOMContentLoaded", function() {
    const creditRadio = document.getElementById("credit_card");
    const subOptions = document.getElementById("credit-sub-options");

    document.querySelectorAll("input[name='payment_method']").forEach(radio => {
        radio.addEventListener("change", function() {
            if (creditRadio.checked) {
                subOptions.classList.remove("d-none");
            } else {
                subOptions.classList.add("d-none");
                document.querySelectorAll("input[name='sub_payment']").forEach(el => el
                    .checked = false);
            }
        });
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\BadmintonShop\resources\views/orders/checkout.blade.php ENDPATH**/ ?>