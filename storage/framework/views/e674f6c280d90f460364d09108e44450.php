

<?php $__env->startSection('title', $product->name . ' - Badminton Shop'); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="<?php echo e(route('products.index')); ?>">Sản phẩm</a></li>
            <li class="breadcrumb-item"><a href="<?php echo e(route('categories.show', $product->category->slug)); ?>"><?php echo e($product->category->name); ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo e($product->name); ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-6">
            <!-- Product Images -->
            <div class="product-images mb-4">
                <div class="main-image mb-3">
                    <img id="mainImage" src="<?php echo e($product->image ? asset('storage/' . $product->image) : 'https://via.placeholder.com/500x400?text=No+Image'); ?>" 
                         class="img-fluid rounded shadow" alt="<?php echo e($product->name); ?>" style="width: 100%; height: 600px; object-fit: cover;">
                </div>
                
                <?php if($product->gallery && count($product->gallery) > 0): ?>
                <div class="thumbnail-images">
                    <div class="row g-2">
                        <!-- Main image thumbnail -->
                        <div class="col-3">
                            <img src="<?php echo e($product->image ? asset('storage/' . $product->image) : 'https://via.placeholder.com/150x120?text=No+Image'); ?>" 
                                 class="img-fluid rounded thumbnail-img cursor-pointer" 
                                 onclick="changeMainImage(this.src)" style="height: 80px; width: 100%; object-fit: cover;">
                        </div>
                        <!-- Gallery thumbnails -->
                        <?php $__currentLoopData = array_slice($product->gallery, 0, 3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-3">
                            <img src="<?php echo e(asset('storage/' . $image)); ?>" 
                                 class="img-fluid rounded thumbnail-img cursor-pointer" 
                                 onclick="changeMainImage(this.src)" style="height: 80px; width: 100%; object-fit: cover;">
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-6">
            <!-- Product Info -->
            <div class="product-info">
                <h1 class="mb-3"><?php echo e($product->name); ?></h1>
                
                <!-- Product Meta -->
                <div class="product-meta mb-4">
                    <p class="text-muted mb-1"><strong>Danh mục:</strong> <a href="<?php echo e(route('categories.show', $product->category->slug)); ?>" class="text-decoration-none"><?php echo e($product->category->name); ?></a></p>
                    <p class="text-muted mb-1"><strong>Thương hiệu:</strong> <?php echo e($product->brand ?? 'Chưa xác định'); ?></p>
                    <p class="text-muted mb-1"><strong>SKU:</strong> <?php echo e($product->sku); ?></p>
                    <p class="text-muted mb-3">
                        <strong>Tình trạng:</strong>
                        <?php if($product->quantity > 0): ?>
                            <span class="text-success">Còn hàng (<?php echo e($product->quantity); ?> sản phẩm)</span>
                        <?php else: ?>
                            <span class="text-danger">Hết hàng</span>
                        <?php endif; ?>
                    </p>

                    <!-- Rating Section -->
                    <div class="rating-section mb-4">
                        <?php if($reviewStats['total'] > 0): ?>
                            <div class="d-flex align-items-center mb-2">
                                <div class="stars me-2">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <?php if($i <= $reviewStats['average']): ?>
                                            <span class="text-warning">★</span>
                                        <?php else: ?>
                                            <span class="text-muted">☆</span>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <span class="fw-bold me-2"><?php echo e($reviewStats['average']); ?>/5</span>
                                <span class="text-muted">(<?php echo e($reviewStats['total']); ?> đánh giá)</span>
                            </div>
                            <div>
                                <a href="<?php echo e(route('products.reviews', $product)); ?>" class="text-decoration-none">Xem tất cả đánh giá</a>
                                <?php if(!$userReview && auth()->check()): ?>
                                    | <a href="<?php echo e(route('reviews.create', $product)); ?>" class="text-decoration-none">Viết đánh giá</a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="mb-2">
                                <span class="text-muted">Chưa có đánh giá</span>
                                <?php if(auth()->check() && !$userReview): ?>
                                    <a href="<?php echo e(route('reviews.create', $product)); ?>" class="text-decoration-none ms-2">Viết đánh giá đầu tiên</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Price -->
                <div class="price-section mb-4">
                    <?php if($product->is_on_sale): ?>
                        <div class="sale-badge mb-2">
                            <span class="badge bg-danger fs-6">Giảm <?php echo e($product->discount_percentage); ?>%</span>
                        </div>
                        <div class="pricing">
                            <span class="current-price text-danger fw-bold" style="font-size: 2rem;"><?php echo e(number_format($product->sale_price)); ?>₫</span>
                            <span class="original-price text-decoration-line-through text-muted ms-2" style="font-size: 1.2rem;"><?php echo e(number_format($product->price)); ?>₫</span>
                        </div>
                        <p class="text-success mb-0">Tiết kiệm <?php echo e(number_format($product->price - $product->sale_price)); ?>₫</p>
                    <?php else: ?>
                        <span class="current-price text-primary fw-bold" style="font-size: 2rem;"><?php echo e(number_format($product->price)); ?>₫</span>
                    <?php endif; ?>
                </div>

                <!-- Add to Cart Form -->
                <?php if(auth()->guard()->check()): ?>
                <div class="add-to-cart-section mb-4">
                    <?php if($product->quantity > 0): ?>
                        <form action="<?php echo e(route('cart.add', $product)); ?>" method="POST" id="addToCartForm">
                            <?php echo csrf_field(); ?>
                            <div class="row align-items-end g-3">
                                <div class="col-auto">
                                    <label for="quantity" class="form-label">Số lượng:</label>
                                    <div class="input-group" style="width: 120px;">
                                        <button class="btn btn-outline-secondary" type="button" onclick="decreaseQuantity()">-</button>
                                        <input type="number" class="form-control text-center" id="quantity" name="quantity" value="1" min="1" max="<?php echo e($product->quantity); ?>">
                                        <button class="btn btn-outline-secondary" type="button" onclick="increaseQuantity()">+</button>
                                    </div>
                                </div>
                                <div class="col">
                                    <button type="submit" class="btn btn-success btn-lg w-100">
                                        <i class="fas fa-shopping-cart"></i> Thêm vào giỏ hàng
                                    </button>
                                </div>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> Sản phẩm hiện tại đã hết hàng. Vui lòng liên hệ để được tư vấn.
                        </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Vui lòng <a href="<?php echo e(route('login')); ?>" class="alert-link">đăng nhập</a> để thêm sản phẩm vào giỏ hàng.
                </div>
                <?php endif; ?>

                <!-- Share Buttons -->
                <div class="share-section">
                    <h6>Chia sẻ:</h6>
                    <div class="social-share">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo e(urlencode(request()->fullUrl())); ?>" target="_blank" class="btn btn-outline-primary btn-sm me-2">
                            <i class="fab fa-facebook"></i> Facebook
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo e(urlencode(request()->fullUrl())); ?>&text=<?php echo e(urlencode($product->name)); ?>" target="_blank" class="btn btn-outline-info btn-sm me-2">
                            <i class="fab fa-twitter"></i> Twitter
                        </a>
                        <a href="https://wa.me/?text=<?php echo e(urlencode($product->name . ' - ' . request()->fullUrl())); ?>" target="_blank" class="btn btn-outline-success btn-sm">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Details Tabs -->
    <div class="row mt-5">
        <div class="col-12">
            <ul class="nav nav-tabs" id="productTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab">
                        Mô tả chi tiết
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping" type="button" role="tab">
                        Chính sách vận chuyển
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab">
                        Đánh giá (<?php echo e($reviewStats['total']); ?>)
                    </button>
                </li>
            </ul>
            <div class="tab-content" id="productTabsContent">
                <div class="tab-pane fade show active" id="description" role="tabpanel">
                    <div class="p-4">
                        <div class="product-full-description">
                            <?php echo nl2br(e($product->description)); ?>

                        </div>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="shipping" role="tabpanel">
                    <div class="p-4">
                        <h5>Chính sách vận chuyển</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-truck text-primary"></i> Miễn phí giao hàng cho đơn hàng trên 500.000₫</li>
                            <li class="mb-2"><i class="fas fa-clock text-primary"></i> Giao hàng trong 2-3 ngày làm việc</li>
                            <li class="mb-2"><i class="fas fa-money-bill-wave text-primary"></i> Hỗ trợ thanh toán COD</li>
                            <li class="mb-2"><i class="fas fa-undo text-primary"></i> Chính sách đổi trả trong vòng 7 ngày</li>
                            <li class="mb-2"><i class="fas fa-phone text-primary"></i> Hỗ trợ khách hàng 24/7</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Reviews Tab -->
                <div class="tab-pane fade" id="reviews" role="tabpanel">
                    <div class="p-4" id="reviews-section">
                        <?php if($reviewStats['total'] > 0): ?>
                            <!-- Rating Summary -->
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <h2 class="display-4 fw-bold text-primary"><?php echo e($reviewStats['average']); ?></h2>
                                        <div class="stars mb-2">
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                                <?php if($i <= $reviewStats['average']): ?>
                                                    <span class="text-warning fs-4">★</span>
                                                <?php else: ?>
                                                    <span class="text-muted fs-4">☆</span>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                        <p class="text-muted"><?php echo e($reviewStats['total']); ?> đánh giá</p>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <?php for($i = 5; $i >= 1; $i--): ?>
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="me-2"><?php echo e($i); ?> sao</span>
                                            <div class="progress flex-grow-1 me-3" style="height: 10px;">
                                                <div class="progress-bar bg-warning" style="width: <?php echo e($reviewStats['percentages'][$i]); ?>%"></div>
                                            </div>
                                            <span class="text-muted"><?php echo e($reviewStats['distribution'][$i]); ?></span>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <!-- Reviews List -->
                            <div class="reviews-list">
                                <?php $__currentLoopData = $product->reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="review-item border-bottom py-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-1"><?php echo e($review->user->name); ?></h6>
                                                <div class="stars mb-1">
                                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                                        <?php if($i <= $review->rating): ?>
                                                            <span class="text-warning">★</span>
                                                        <?php else: ?>
                                                            <span class="text-muted">☆</span>
                                                        <?php endif; ?>
                                                    <?php endfor; ?>
                                                </div>
                                                <?php if($review->is_verified_purchase): ?>
                                                    <span class="badge bg-success">Đã mua sản phẩm</span>
                                                <?php endif; ?>
                                            </div>
                                            <small class="text-muted"><?php echo e($review->created_at->diffForHumans()); ?></small>
                                        </div>
                                        
                                        <?php if($review->title): ?>
                                            <h6 class="fw-bold"><?php echo e($review->title); ?></h6>
                                        <?php endif; ?>
                                        
                                        <?php if($review->comment): ?>
                                            <p class="mb-2"><?php echo e($review->comment); ?></p>
                                        <?php endif; ?>
                                        
                                        <?php if($review->pros && count($review->pros) > 0): ?>
                                            <div class="mb-2">
                                                <small class="text-success fw-bold">Ưu điểm:</small>
                                                <ul class="list-unstyled ms-3">
                                                    <?php $__currentLoopData = $review->pros; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <li><small>+ <?php echo e($pro); ?></small></li>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if($review->cons && count($review->cons) > 0): ?>
                                            <div class="mb-2">
                                                <small class="text-danger fw-bold">Nhược điểm:</small>
                                                <ul class="list-unstyled ms-3">
                                                    <?php $__currentLoopData = $review->cons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $con): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <li><small>- <?php echo e($con); ?></small></li>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if($review->helpful_count > 0): ?>
                                            <div class="mt-2">
                                                <small class="text-muted"><?php echo e($review->helpful_count); ?> người thấy hữu ích</small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>

                            <!-- View All Reviews Button -->
                            <?php if($product->reviews_count > 5): ?>
                                <div class="text-center mt-4">
                                    <a href="<?php echo e(route('products.reviews', $product)); ?>" class="btn btn-outline-primary">
                                        Xem tất cả <?php echo e($product->reviews_count); ?> đánh giá
                                    </a>
                                </div>
                            <?php endif; ?>

                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-star text-muted" style="font-size: 3rem;"></i>
                                <h5 class="mt-3">Chưa có đánh giá nào</h5>
                                <p class="text-muted">Hãy là người đầu tiên đánh giá sản phẩm này!</p>
                                <?php if(auth()->check()): ?>
                                    <a href="<?php echo e(route('reviews.create', $product)); ?>" class="btn btn-primary">Viết đánh giá</a>
                                <?php else: ?>
                                    <a href="<?php echo e(route('login')); ?>" class="btn btn-primary">Đăng nhập để viết đánh giá</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Write Review Button -->
                        <?php if(auth()->check() && !$userReview && $reviewStats['total'] > 0): ?>
                            <div class="text-center mt-4">
                                <a href="<?php echo e(route('reviews.create', $product)); ?>" class="btn btn-success">
                                    <i class="fas fa-star"></i> Viết đánh giá
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if($relatedProducts && $relatedProducts->count() > 0): ?>
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="mb-4">Sản phẩm liên quan</h3>
            <div class="row">
                <?php $__currentLoopData = $relatedProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $relatedProduct): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card product-card h-100 border-0 shadow-sm">
                        <?php if($relatedProduct->is_on_sale): ?>
                            <span class="badge bg-danger badge-sale">-<?php echo e($relatedProduct->discount_percentage); ?>%</span>
                        <?php endif; ?>
                        <img src="<?php echo e($relatedProduct->image ? asset('storage/' . $relatedProduct->image) : 'https://via.placeholder.com/300x200?text=No+Image'); ?>" 
                             class="card-img-top" alt="<?php echo e($relatedProduct->name); ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title"><?php echo e($relatedProduct->name); ?></h6>
                            <div class="mt-auto">
                                <div class="mb-2">
                                    <?php if($relatedProduct->is_on_sale): ?>
                                        <span class="text-danger fw-bold"><?php echo e(number_format($relatedProduct->sale_price)); ?>₫</span><br>
                                        <small class="text-decoration-line-through text-muted"><?php echo e(number_format($relatedProduct->price)); ?>₫</small>
                                    <?php else: ?>
                                        <span class="text-primary fw-bold"><?php echo e(number_format($relatedProduct->price)); ?>₫</span>
                                    <?php endif; ?>
                                </div>
                                <a href="<?php echo e(route('products.show', $relatedProduct->slug)); ?>" class="btn btn-outline-primary btn-sm w-100">Xem chi tiết</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function changeMainImage(src) {
    document.getElementById('mainImage').src = src;
}

function increaseQuantity() {
    const quantityInput = document.getElementById('quantity');
    const currentValue = parseInt(quantityInput.value);
    const maxValue = parseInt(quantityInput.max);
    
    if (currentValue < maxValue) {
        quantityInput.value = currentValue + 1;
    }
}

function decreaseQuantity() {
    const quantityInput = document.getElementById('quantity');
    const currentValue = parseInt(quantityInput.value);
    const minValue = parseInt(quantityInput.min);
    
    if (currentValue > minValue) {
        quantityInput.value = currentValue - 1;
    }
}
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.badge-sale {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 10;
}
.product-card {
    transition: transform 0.2s;
}
.product-card:hover {
    transform: translateY(-5px);
}
.thumbnail-img {
    border: 2px solid transparent;
    transition: all 0.3s ease;
}
.thumbnail-img:hover {
    border-color: #0d6efd;
}
.cursor-pointer {
    cursor: pointer;
}
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\xampp\htdocs\BadmintonShop\resources\views/products/show.blade.php ENDPATH**/ ?>