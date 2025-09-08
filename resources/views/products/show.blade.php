@extends('layouts.app')

@section('title', $product->name . ' - Badminton Shop')

@section('content')
<div class="container">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Sản phẩm</a></li>
            <li class="breadcrumb-item"><a href="{{ route('categories.show', $product->category->slug) }}">{{ $product->category->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-6">
            <!-- Product Images -->
            <div class="product-images mb-4">
                <div class="main-image mb-3">
                    <img id="mainImage" src="{{ $product->image ? asset('storage/' . $product->image) : 'https://via.placeholder.com/500x400?text=No+Image' }}" 
                         class="img-fluid rounded shadow" alt="{{ $product->name }}" style="width: 100%; height: 600px; object-fit: cover;">
                </div>
                
                @if($product->gallery && count($product->gallery) > 0)
                <div class="thumbnail-images">
                    <div class="row g-2">
                        <!-- Main image thumbnail -->
                        <div class="col-3">
                            <img src="{{ $product->image ? asset('storage/' . $product->image) : 'https://via.placeholder.com/150x120?text=No+Image' }}" 
                                 class="img-fluid rounded thumbnail-img cursor-pointer" 
                                 onclick="changeMainImage(this.src)" style="height: 80px; width: 100%; object-fit: cover;">
                        </div>
                        <!-- Gallery thumbnails -->
                        @foreach(array_slice($product->gallery, 0, 3) as $image)
                        <div class="col-3">
                            <img src="{{ asset('storage/' . $image) }}" 
                                 class="img-fluid rounded thumbnail-img cursor-pointer" 
                                 onclick="changeMainImage(this.src)" style="height: 80px; width: 100%; object-fit: cover;">
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="col-md-6">
            <!-- Product Info -->
            <div class="product-info">
                <h1 class="mb-3">{{ $product->name }}</h1>
                
                <!-- Product Meta -->
                <div class="product-meta mb-4">
                    <p class="text-muted mb-1"><strong>Danh mục:</strong> <a href="{{ route('categories.show', $product->category->slug) }}" class="text-decoration-none">{{ $product->category->name }}</a></p>
                    <p class="text-muted mb-1"><strong>Thương hiệu:</strong> {{ $product->brand ?? 'Chưa xác định' }}</p>
                    <p class="text-muted mb-1"><strong>SKU:</strong> {{ $product->sku }}</p>
                    <p class="text-muted mb-3">
                        <strong>Tình trạng:</strong>
                        @if($product->quantity > 0)
                            <span class="text-success">Còn hàng ({{ $product->quantity }} sản phẩm)</span>
                        @else
                            <span class="text-danger">Hết hàng</span>
                        @endif
                    </p>

                    <!-- Rating Section -->
                    <div class="rating-section mb-4">
                        @if($reviewStats['total'] > 0)
                            <div class="d-flex align-items-center mb-2">
                                <div class="stars me-2">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $reviewStats['average'])
                                            <span class="text-warning">★</span>
                                        @else
                                            <span class="text-muted">☆</span>
                                        @endif
                                    @endfor
                                </div>
                                <span class="fw-bold me-2">{{ $reviewStats['average'] }}/5</span>
                                <span class="text-muted">({{ $reviewStats['total'] }} đánh giá)</span>
                            </div>
                            <div>
                                <a href="{{ route('products.reviews', $product) }}" class="text-decoration-none">Xem tất cả đánh giá</a>
                                @if(!$userReview && auth()->check())
                                    | <a href="{{ route('reviews.create', $product) }}" class="text-decoration-none">Viết đánh giá</a>
                                @endif
                            </div>
                        @else
                            <div class="mb-2">
                                <span class="text-muted">Chưa có đánh giá</span>
                                @if(auth()->check() && !$userReview)
                                    <a href="{{ route('reviews.create', $product) }}" class="text-decoration-none ms-2">Viết đánh giá đầu tiên</a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Price -->
                <div class="price-section mb-4">
                    @if($product->is_on_sale)
                        <div class="sale-badge mb-2">
                            <span class="badge bg-danger fs-6">Giảm {{ $product->discount_percentage }}%</span>
                        </div>
                        <div class="pricing">
                            <span class="current-price text-danger fw-bold" style="font-size: 2rem;">{{ number_format($product->sale_price) }}₫</span>
                            <span class="original-price text-decoration-line-through text-muted ms-2" style="font-size: 1.2rem;">{{ number_format($product->price) }}₫</span>
                        </div>
                        <p class="text-success mb-0">Tiết kiệm {{ number_format($product->price - $product->sale_price) }}₫</p>
                    @else
                        <span class="current-price text-primary fw-bold" style="font-size: 2rem;">{{ number_format($product->price) }}₫</span>
                    @endif
                </div>

                <!-- Add to Cart Form -->
                @auth
                <div class="add-to-cart-section mb-4">
                    @if($product->quantity > 0)
                        <form action="{{ route('cart.add', $product) }}" method="POST" id="addToCartForm">
                            @csrf
                            <div class="row align-items-end g-3">
                                <div class="col-auto">
                                    <label for="quantity" class="form-label">Số lượng:</label>
                                    <div class="input-group" style="width: 120px;">
                                        <button class="btn btn-outline-secondary" type="button" onclick="decreaseQuantity()">-</button>
                                        <input type="number" class="form-control text-center" id="quantity" name="quantity" value="1" min="1" max="{{ $product->quantity }}">
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
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> Sản phẩm hiện tại đã hết hàng. Vui lòng liên hệ để được tư vấn.
                        </div>
                    @endif
                </div>
                @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Vui lòng <a href="{{ route('login') }}" class="alert-link">đăng nhập</a> để thêm sản phẩm vào giỏ hàng.
                </div>
                @endauth

                <!-- Share Buttons -->
                <div class="share-section">
                    <h6>Chia sẻ:</h6>
                    <div class="social-share">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}" target="_blank" class="btn btn-outline-primary btn-sm me-2">
                            <i class="fab fa-facebook"></i> Facebook
                        </a>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->fullUrl()) }}&text={{ urlencode($product->name) }}" target="_blank" class="btn btn-outline-info btn-sm me-2">
                            <i class="fab fa-twitter"></i> Twitter
                        </a>
                        <a href="https://wa.me/?text={{ urlencode($product->name . ' - ' . request()->fullUrl()) }}" target="_blank" class="btn btn-outline-success btn-sm">
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
                        Đánh giá ({{ $reviewStats['total'] }})
                    </button>
                </li>
            </ul>
            <div class="tab-content" id="productTabsContent">
                <div class="tab-pane fade show active" id="description" role="tabpanel">
                    <div class="p-4">
                        <div class="product-full-description">
                            {!! nl2br(e($product->description)) !!}
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
                        @if($reviewStats['total'] > 0)
                            <!-- Rating Summary -->
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <h2 class="display-4 fw-bold text-primary">{{ $reviewStats['average'] }}</h2>
                                        <div class="stars mb-2">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $reviewStats['average'])
                                                    <span class="text-warning fs-4">★</span>
                                                @else
                                                    <span class="text-muted fs-4">☆</span>
                                                @endif
                                            @endfor
                                        </div>
                                        <p class="text-muted">{{ $reviewStats['total'] }} đánh giá</p>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    @for($i = 5; $i >= 1; $i--)
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="me-2">{{ $i }} sao</span>
                                            <div class="progress flex-grow-1 me-3" style="height: 10px;">
                                                <div class="progress-bar bg-warning" style="width: {{ $reviewStats['percentages'][$i] }}%"></div>
                                            </div>
                                            <span class="text-muted">{{ $reviewStats['distribution'][$i] }}</span>
                                        </div>
                                    @endfor
                                </div>
                            </div>

                            <!-- Reviews List -->
                            <div class="reviews-list">
                                @foreach($product->reviews as $review)
                                    <div class="review-item border-bottom py-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-1">{{ $review->user->name }}</h6>
                                                <div class="stars mb-1">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        @if($i <= $review->rating)
                                                            <span class="text-warning">★</span>
                                                        @else
                                                            <span class="text-muted">☆</span>
                                                        @endif
                                                    @endfor
                                                </div>
                                                @if($review->is_verified_purchase)
                                                    <span class="badge bg-success">Đã mua sản phẩm</span>
                                                @endif
                                            </div>
                                            <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                                        </div>
                                        
                                        @if($review->title)
                                            <h6 class="fw-bold">{{ $review->title }}</h6>
                                        @endif
                                        
                                        @if($review->comment)
                                            <p class="mb-2">{{ $review->comment }}</p>
                                        @endif
                                        
                                        @if($review->pros && count($review->pros) > 0)
                                            <div class="mb-2">
                                                <small class="text-success fw-bold">Ưu điểm:</small>
                                                <ul class="list-unstyled ms-3">
                                                    @foreach($review->pros as $pro)
                                                        <li><small>+ {{ $pro }}</small></li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                        
                                        @if($review->cons && count($review->cons) > 0)
                                            <div class="mb-2">
                                                <small class="text-danger fw-bold">Nhược điểm:</small>
                                                <ul class="list-unstyled ms-3">
                                                    @foreach($review->cons as $con)
                                                        <li><small>- {{ $con }}</small></li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                        
                                        @if($review->helpful_count > 0)
                                            <div class="mt-2">
                                                <small class="text-muted">{{ $review->helpful_count }} người thấy hữu ích</small>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <!-- View All Reviews Button -->
                            @if($product->reviews_count > 5)
                                <div class="text-center mt-4">
                                    <a href="{{ route('products.reviews', $product) }}" class="btn btn-outline-primary">
                                        Xem tất cả {{ $product->reviews_count }} đánh giá
                                    </a>
                                </div>
                            @endif

                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-star text-muted" style="font-size: 3rem;"></i>
                                <h5 class="mt-3">Chưa có đánh giá nào</h5>
                                <p class="text-muted">Hãy là người đầu tiên đánh giá sản phẩm này!</p>
                                @if(auth()->check())
                                    <a href="{{ route('reviews.create', $product) }}" class="btn btn-primary">Viết đánh giá</a>
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-primary">Đăng nhập để viết đánh giá</a>
                                @endif
                            </div>
                        @endif

                        <!-- Write Review Button -->
                        @if(auth()->check() && !$userReview && $reviewStats['total'] > 0)
                            <div class="text-center mt-4">
                                <a href="{{ route('reviews.create', $product) }}" class="btn btn-success">
                                    <i class="fas fa-star"></i> Viết đánh giá
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts && $relatedProducts->count() > 0)
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="mb-4">Sản phẩm liên quan</h3>
            <div class="row">
                @foreach($relatedProducts as $relatedProduct)
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card product-card h-100 border-0 shadow-sm">
                        @if($relatedProduct->is_on_sale)
                            <span class="badge bg-danger badge-sale">-{{ $relatedProduct->discount_percentage }}%</span>
                        @endif
                        <img src="{{ $relatedProduct->image ? asset('storage/' . $relatedProduct->image) : 'https://via.placeholder.com/300x200?text=No+Image' }}" 
                             class="card-img-top" alt="{{ $relatedProduct->name }}" style="height: 200px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title">{{ $relatedProduct->name }}</h6>
                            <div class="mt-auto">
                                <div class="mb-2">
                                    @if($relatedProduct->is_on_sale)
                                        <span class="text-danger fw-bold">{{ number_format($relatedProduct->sale_price) }}₫</span><br>
                                        <small class="text-decoration-line-through text-muted">{{ number_format($relatedProduct->price) }}₫</small>
                                    @else
                                        <span class="text-primary fw-bold">{{ number_format($relatedProduct->price) }}₫</span>
                                    @endif
                                </div>
                                <a href="{{ route('products.show', $relatedProduct->slug) }}" class="btn btn-outline-primary btn-sm w-100">Xem chi tiết</a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
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
@endpush

@push('styles')
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
@endpush
@endsection
