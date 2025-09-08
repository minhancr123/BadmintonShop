@extends('layouts.app')

@section('title', 'Trang chủ - Badminton Shop')

@section('content')
<!-- Hero Section -->
<div class="hero-section position-relative overflow-hidden">
    <div class="hero-bg"></div>
    <div class="container position-relative">
        <div class="row align-items-center min-vh-75">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="display-3 fw-bold text-white mb-4">
                        Đẳng Cấp 
                        <span class="text-warning">Cầu Lông</span>
                    </h1>
                    <p class="lead text-white-50 mb-4 fs-5">
                        Khám phá bộ sưu tập vợt cầu lông cao cấp từ các thương hiệu hàng đầu thế giới. 
                        Nơi những tay vợt chuyên nghiệp tin tưởng lựa chọn.
                    </p>
                    <div class="d-flex gap-3 mb-4">
                        <a href="{{ route('products.index') }}" class="btn btn-warning btn-lg px-5 py-3 fw-bold">
                            <i class="fas fa-shopping-bag me-2"></i>Khám Phá Ngay
                        </a>
                        <a href="#featured" class="btn btn-outline-light btn-lg px-4 py-3">
                            <i class="fas fa-play me-2"></i>Xem Sản Phẩm
                        </a>
                    </div>
                    <div class="hero-features d-flex gap-4 text-white-50">
                        <div><i class="fas fa-shipping-fast me-2"></i>Giao hàng 24h</div>
                        <div><i class="fas fa-medal me-2"></i>Chính hãng 100%</div>
                        <div><i class="fas fa-tools me-2"></i>Bảo hành tận tâm</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-image text-center">
                    <div class="floating-racket">
                        <img src="https://images.unsplash.com/photo-1544919982-b61976f0ba43?w=600&h=600&fit=crop" 
                             alt="Badminton Racket" class="img-fluid rounded-circle shadow-lg" 
                             style="width: 400px; height: 400px; object-fit: cover;">
                        <div class="floating-shuttlecock">
                            <i class="fas fa-futbol text-warning" style="font-size: 3rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="stats-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-md-3 col-6 mb-4">
                <div class="stat-card text-center">
                    <div class="stat-icon bg-primary text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="fas fa-table-tennis" style="font-size: 2rem;"></i>
                    </div>
                    <h3 class="fw-bold text-primary mb-1">{{ $stats['total_products'] }}+</h3>
                    <p class="text-muted mb-0">Sản phẩm chất lượng</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="stat-card text-center">
                    <div class="stat-icon bg-success text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="fas fa-layer-group" style="font-size: 2rem;"></i>
                    </div>
                    <h3 class="fw-bold text-success mb-1">{{ $stats['total_categories'] }}+</h3>
                    <p class="text-muted mb-0">Danh mục đa dạng</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="stat-card text-center">
                    <div class="stat-icon bg-warning text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="fas fa-fire" style="font-size: 2rem;"></i>
                    </div>
                    <h3 class="fw-bold text-warning mb-1">{{ $stats['products_on_sale'] }}+</h3>
                    <p class="text-muted mb-0">Ưu đại hấp dẫn</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="stat-card text-center">
                    <div class="stat-icon bg-info text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="fas fa-users" style="font-size: 2rem;"></i>
                    </div>
                    <h3 class="fw-bold text-info mb-1">1000+</h3>
                    <p class="text-muted mb-0">Khách hàng tin tưởng</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Featured Products -->
@if($featuredProducts->count() > 0)
<section id="featured" class="featured-section py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <div class="d-inline-flex align-items-center mb-3">
                <i class="fas fa-star text-warning me-2" style="font-size: 2rem;"></i>
                <h2 class="display-5 fw-bold mb-0">Sản Phẩm Nổi Bật</h2>
            </div>
            <p class="lead text-muted">Những sản phẩm được ưa chuộng nhất từ các thương hiệu hàng đầu</p>
            <div class="divider mx-auto bg-warning" style="width: 80px; height: 4px; border-radius: 2px;"></div>
        </div>
        <div class="row">
            @foreach($featuredProducts as $product)
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="product-card h-100 border-0 shadow-sm position-relative overflow-hidden">
                    @if($product->is_on_sale)
                    <div class="sale-badge position-absolute top-0 end-0 bg-danger text-white px-3 py-1 rounded-start">
                        <i class="fas fa-fire me-1"></i>
                        -{{ $product->discount_percentage }}% OFF
                    </div>
                    @endif
                    
                    <div class="product-image-wrapper position-relative overflow-hidden">
                        <img src="{{ $product->image ? asset('storage/' . $product->image) : 'https://images.unsplash.com/photo-1544919982-b61976f0ba43?w=400&h=300&fit=crop' }}" 
                             class="card-img-top product-image" 
                             alt="{{ $product->name }}"
                             style="height: 250px; object-fit: cover; transition: transform 0.3s ease;">
                        <div class="product-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                            <a href="{{ route('products.show', $product->slug) }}" 
                               class="btn btn-primary btn-lg rounded-circle shadow-lg">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-body p-4 d-flex flex-column">
                        <h5 class="card-title fw-bold mb-3" style="height: 2.5em; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">{{ $product->name }}</h5>
                        <p class="card-text text-muted small mb-3 flex-grow-1" style="height: 3em; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">{{ Str::limit($product->description, 80) }}</p>
                        <div class="price-wrapper mb-3">
                            @if($product->is_on_sale)
                                <span class="current-price text-danger fw-bold fs-5">
                                    {{ number_format($product->sale_price, 0, ',', '.') }}đ
                                </span>
                                <span class="original-price text-muted text-decoration-line-through ms-2">
                                    {{ number_format($product->price, 0, ',', '.') }}đ
                                </span>
                            @else
                                <span class="current-price text-primary fw-bold fs-5">
                                    {{ number_format($product->price, 0, ',', '.') }}đ
                                </span>
                            @endif
                        </div>
                        <div class="d-grid mt-auto">
                            <form action="{{ route('cart.add', $product->id) }}" method="POST" class="add-to-cart-form">
                                @csrf
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-shopping-cart me-2"></i>Thêm vào giỏ
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-4">
            <a href="{{ route('products.index') }}" class="btn btn-outline-primary btn-lg px-5">
                <i class="fas fa-arrow-right me-2"></i>Xem tất cả sản phẩm
            </a>
        </div>
    </div>
</section>
@endif

<!-- Popular Categories -->
@if($popularCategories->count() > 0)
<section class="categories-section py-5 bg-light">
    <div class="container">
        <div class="section-header text-center mb-5">
            <div class="d-inline-flex align-items-center mb-3">
                <i class="fas fa-layer-group text-success me-2" style="font-size: 2rem;"></i>
                <h2 class="display-5 fw-bold mb-0">Danh Mục Phổ Biến</h2>
            </div>
            <p class="lead text-muted">Khám phá các danh mục sản phẩm đa dạng của chúng tôi</p>
            <div class="divider mx-auto bg-success" style="width: 80px; height: 4px; border-radius: 2px;"></div>
        </div>
        <div class="row">
            @foreach($popularCategories as $category)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="category-card h-100 border-0 shadow-sm position-relative overflow-hidden bg-white rounded-3">
                    <div class="category-header p-4 text-center">
                        <div class="category-icon mb-3">
                            @switch($category->name)
                                @case('Vợt cầu lông')
                                    <div class="icon-wrapper bg-primary bg-gradient text-white rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                        <i class="fas fa-table-tennis" style="font-size: 2.5rem;"></i>
                                    </div>
                                    @break
                                @case('Giày cầu lông')
                                    <div class="icon-wrapper bg-success bg-gradient text-white rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                        <i class="fas fa-running" style="font-size: 2.5rem;"></i>
                                    </div>
                                    @break
                                @case('Quần áo thể thao')
                                    <div class="icon-wrapper bg-warning bg-gradient text-white rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                        <i class="fas fa-tshirt" style="font-size: 2.5rem;"></i>
                                    </div>
                                    @break
                                @case('Phụ kiện')
                                    <div class="icon-wrapper bg-info bg-gradient text-white rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                        <i class="fas fa-tools" style="font-size: 2.5rem;"></i>
                                    </div>
                                    @break
                                @default
                                    <div class="icon-wrapper bg-secondary bg-gradient text-white rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                        <i class="fas fa-cube" style="font-size: 2.5rem;"></i>
                                    </div>
                            @endswitch
                        </div>
                        <h4 class="fw-bold mb-2">{{ $category->name }}</h4>
                        <p class="text-muted mb-3">{{ $category->products_count }} sản phẩm</p>
                        <a href="{{ route('categories.show', $category->slug) }}" 
                           class="btn btn-outline-primary btn-lg stretched-link">
                            <i class="fas fa-arrow-right me-2"></i>Khám phá ngay
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Sale Products -->
@if($saleProducts->count() > 0)
<section class="sale-section py-5 bg-gradient-primary text-white">
    <div class="container">
        <div class="section-header text-center mb-5">
            <div class="d-inline-flex align-items-center mb-3">
                <i class="fas fa-fire text-warning me-2" style="font-size: 2rem;"></i>
                <h2 class="display-5 fw-bold mb-0 text-white">Siêu Khuyến Mãi</h2>
            </div>
            <p class="lead mb-0">Cơ hội vàng để sở hữu những sản phẩm chất lượng với giá ưu đãi</p>
            <div class="divider mx-auto bg-warning" style="width: 80px; height: 4px; border-radius: 2px;"></div>
        </div>
        <div class="row">
            @foreach($saleProducts->take(4) as $product)
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="sale-product-card bg-white text-dark rounded-3 shadow-lg overflow-hidden h-100">
                    <div class="position-relative">
                        <img src="{{ $product->image ? asset('storage/' . $product->image) : 'https://images.unsplash.com/photo-1544919982-b61976f0ba43?w=400&h=250&fit=crop' }}" 
                             class="card-img-top" 
                             alt="{{ $product->name }}"
                             style="height: 200px; object-fit: cover;">
                        <div class="sale-overlay position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-flex align-items-center justify-content-center">
                            <div class="sale-badge bg-danger text-white px-3 py-2 rounded-pill shadow">
                                <i class="fas fa-bolt me-1"></i>
                                -{{ $product->discount_percentage }}% OFF
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4 d-flex flex-column">
                        <h5 class="card-title fw-bold mb-2" style="height: 2.5em; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">{{ $product->name }}</h5>
                        <div class="price-comparison mb-3 flex-grow-1">
                            <div class="d-flex align-items-center gap-3">
                                <span class="new-price text-danger fw-bold fs-4">
                                    {{ number_format($product->sale_price, 0, ',', '.') }}đ
                                </span>
                                <span class="old-price text-muted text-decoration-line-through">
                                    {{ number_format($product->price, 0, ',', '.') }}đ
                                </span>
                            </div>
                            <small class="text-success fw-bold">
                                Tiết kiệm: {{ number_format($product->price - $product->sale_price, 0, ',', '.') }}đ
                            </small>
                        </div>
                        <div class="d-grid mt-auto">
                            <a href="{{ route('products.show', $product->slug) }}" 
                               class="btn btn-danger btn-lg fw-bold">
                                <i class="fas fa-shopping-cart me-2"></i>Mua Ngay
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Newsletter Section -->
<!-- <section class="newsletter-section py-5 bg-dark text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="newsletter-content">
                    <h3 class="fw-bold mb-3">
                        <i class="fas fa-envelope-open text-warning me-2"></i>
                        Đăng Ký Nhận Tin
                    </h3>
                    <p class="lead mb-0">Nhận thông tin về sản phẩm mới, khuyến mãi đặc biệt và tips chơi cầu lông từ các chuyên gia</p>
                </div>
            </div>
            <div class="col-lg-6">
                <form class="newsletter-form">
                    <div class="input-group input-group-lg">
                        <input type="email" class="form-control" placeholder="Nhập email của bạn...">
                        <button class="btn btn-warning fw-bold px-4" type="submit">
                            <i class="fas fa-paper-plane me-2"></i>Đăng Ký
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section> -->

<style>
/* Custom CSS for Welcome Page */
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 80vh;
    position: relative;
}

.hero-bg {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('https://images.unsplash.com/photo-1544919982-b61976f0ba43?w=1920&h=1080&fit=crop') center/cover;
    opacity: 0.1;
}

.min-vh-75 {
    min-height: 75vh;
}

.floating-racket {
    position: relative;
    animation: float 3s ease-in-out infinite;
}

.floating-shuttlecock {
    position: absolute;
    top: -20px;
    right: -20px;
    animation: bounce 2s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

@keyframes bounce {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

.stat-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.product-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 15px !important;
    height: 100% !important;
    display: flex !important;
    flex-direction: column !important;
}

.product-card .card-body {
    flex: 1 !important;
    display: flex !important;
    flex-direction: column !important;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
}

.product-image-wrapper:hover .product-image {
    transform: scale(1.1);
}

.product-overlay {
    background: rgba(0,0,0,0.7);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.product-card:hover .product-overlay {
    opacity: 1;
}

.category-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.sale-product-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    height: 100% !important;
    display: flex !important;
    flex-direction: column !important;
}

.sale-product-card .card-body {
    flex: 1 !important;
    display: flex !important;
    flex-direction: column !important;
}

.sale-product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.newsletter-form .form-control {
    border: 3px solid #ffc107;
    border-right: none;
}

.newsletter-form .form-control:focus {
    box-shadow: none;
    border-color: #ffc107;
}

.section-header .divider {
    margin-top: 1rem;
}
</style>

@endsection
