@extends('layouts.app')

@section('title', 'Danh mục sản phẩm - Badminton Shop')

@section('content')
<div class="container">
    <!-- Hero Banner -->
    <div class="hero-banner text-center py-5 mb-5">
        <div class="hero-content">
            <h1 class="display-4 fw-bold text-white mb-3">Danh mục sản phẩm</h1>
            <p class="lead text-white-50 mb-4">Khám phá bộ sưu tập đồ cầu lông chuyên nghiệp</p>
            <div class="hero-icons">
                <i class="fas fa-medal text-warning me-3" style="font-size: 2rem;"></i>
                <i class="fas fa-award text-warning me-3" style="font-size: 2rem;"></i>
                <i class="fas fa-trophy text-warning" style="font-size: 2rem;"></i>
            </div>
        </div>
    </div>

    <!-- Categories Grid -->
    <div class="row g-4">
        @forelse($categories as $category)
            <div class="col-lg-4 col-md-6">
                <div class="card category-card h-100 border-0 shadow-sm overflow-hidden">
                    <!-- Card Header with Gradient -->
                    <div class="card-header-gradient text-center py-4">
                        <!-- Category Icon -->
                        <div class="category-icon mb-3">
                            @php
                                $iconClass = 'fas fa-tag'; // Default icon
                                $iconColor = 'text-white';
                                $bgGradient = 'bg-primary';
                                
                                // Set specific icons and colors based on category name
                                $categoryName = strtolower($category->name);
                                if (str_contains($categoryName, 'Vợt') || str_contains($categoryName, 'racket')) {
                                    $iconClass = 'fas fa-table-tennis';
                                    $bgGradient = 'bg-gradient-danger';
                                } elseif (str_contains($categoryName, 'giày') || str_contains($categoryName, 'shoes')) {
                                    $iconClass = 'fas fa-shoe-prints';
                                    $bgGradient = 'bg-gradient-success';
                                } elseif (str_contains($categoryName, 'áo') || str_contains($categoryName, 'shirt') || str_contains($categoryName, 'clothing')) {
                                    $iconClass = 'fas fa-tshirt';
                                    $bgGradient = 'bg-gradient-info';
                                } elseif (str_contains($categoryName, 'Túi') || str_contains($categoryName, 'balo') || str_contains($categoryName, 'bag')) {
                                    $iconClass = 'fas fa-bag-shopping';
                                    $bgGradient = 'bg-gradient-warning';
                                } elseif (str_contains($categoryName, 'phụ kiện') || str_contains($categoryName, 'accessories')) {
                                    $iconClass = 'fas fa-tools';
                                    $bgGradient = 'bg-gradient-secondary';
                                } elseif (str_contains($categoryName, 'cầu') || str_contains($categoryName, 'shuttlecock')) {
                                    $iconClass = 'fas fa-feather';
                                    $bgGradient = 'bg-gradient-success';
                                }
                            @endphp
                            <div class="icon-circle {{ $bgGradient }} mx-auto d-flex align-items-center justify-content-center">
                                <i class="{{ $iconClass }} {{ $iconColor }}" style="font-size: 2.5rem;"></i>
                            </div>
                        </div>

                        <!-- Category Title -->
                        <h4 class="card-title mb-0 text-white fw-bold">{{ $category->name }}</h4>
                    </div>
                    <!-- Card Body -->
                    <div class="card-body p-4">
                        <p class="card-text text-muted mb-4">{{ $category->description ?: 'Khám phá các sản phẩm chất lượng trong danh mục này' }}</p>
                        
                        <!-- Statistics -->
                        <div class="category-stats mb-4">
                            <div class="row text-center g-3">
                                <div class="col-6">
                                    <div class="stat-card p-3 bg-light rounded">
                                        <div class="stat-number text-primary fw-bold fs-4">{{ $category->products_count ?? 0 }}</div>
                                        <div class="stat-label text-muted small">Sản phẩm</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-card p-3 bg-light rounded">
                                        <div class="stat-number text-success fw-bold fs-4">
                                            @if($category->products && $category->products->where('is_active', true)->where('quantity', '>', 0)->count() > 0)
                                                {{ $category->products->where('is_active', true)->where('quantity', '>', 0)->count() }}
                                            @else
                                                0
                                            @endif
                                        </div>
                                        <div class="stat-label text-muted small">Còn hàng</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Price Range -->
                        @if($category->products && $category->products->count() > 0)
                            @php
                                $activeProducts = $category->products->where('is_active', true);
                                if ($activeProducts->count() > 0) {
                                    $minPrice = $activeProducts->min(function($product) {
                                        return $product->is_on_sale ? $product->sale_price : $product->price;
                                    });
                                    $maxPrice = $activeProducts->max(function($product) {
                                        return $product->is_on_sale ? $product->sale_price : $product->price;
                                    });
                                }
                            @endphp
                            @if(isset($minPrice) && isset($maxPrice))
                            <div class="price-range mb-4 p-3 bg-gradient-light rounded">
                                <div class="text-center">
                                    <small class="text-muted d-block mb-1">Khoảng giá</small>
                                    <div class="price-text">
                                        <span class="fw-bold text-dark">{{ number_format($minPrice) }}₫</span>
                                        <span class="text-muted mx-2">-</span>
                                        <span class="fw-bold text-dark">{{ number_format($maxPrice) }}₫</span>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endif

                        <!-- Action Button -->
                        <div class="d-grid mt-auto">
                            <a href="{{ route('categories.show', $category->slug) }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-store me-2"></i>
                                Khám phá ngay
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                    <h4>Chưa có danh mục nào</h4>
                    <p class="text-muted">Các danh mục sản phẩm sẽ được cập nhật sớm.</p>
                    <a href="{{ route('home') }}" class="btn btn-primary">Quay về trang chủ</a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Popular Categories Section -->
    @if($popularCategories && $popularCategories->count() > 0)
    <div class="row mt-5">
        <div class="col-12">
            <hr class="my-5">
            <h3 class="text-center mb-4">Danh mục phổ biến</h3>
            <div class="row">
                @foreach($popularCategories->take(3) as $popularCategory)
                <div class="col-md-4 mb-4">
                    <div class="card bg-light border-0">
                        <div class="card-body text-center">
                            <i class="fas fa-star text-warning mb-2" style="font-size: 2rem;"></i>
                            <h5>{{ $popularCategory->name }}</h5>
                            <p class="text-muted small">{{ $popularCategory->products_count }} sản phẩm</p>
                            <a href="{{ route('categories.show', $popularCategory->slug) }}" class="btn btn-sm btn-warning">
                                Xem ngay
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Featured Products from Categories -->
    @if($featuredProducts && $featuredProducts->count() > 0)
    <div class="row mt-5">
        <div class="col-12">
            <hr class="my-5">
            <h3 class="text-center mb-4">Sản phẩm nổi bật từ các danh mục</h3>
            <div class="row">
                @foreach($featuredProducts->take(4) as $product)
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card product-card h-100 border-0 shadow-sm">
                        @if($product->is_on_sale)
                            <span class="badge bg-danger badge-sale">-{{ $product->discount_percentage }}%</span>
                        @endif
                        <img src="{{ $product->image ? asset('storage/' . $product->image) : 'https://via.placeholder.com/300x200?text=No+Image' }}" 
                             class="card-img-top" alt="{{ $product->name }}" style="height: 180px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title">{{ $product->name }}</h6>
                            <p class="text-muted small">{{ $product->category->name }}</p>
                            <div class="mt-auto">
                                <div class="mb-2">
                                    @if($product->is_on_sale)
                                        <span class="text-danger fw-bold">{{ number_format($product->sale_price) }}₫</span><br>
                                        <small class="text-decoration-line-through text-muted">{{ number_format($product->price) }}₫</small>
                                    @else
                                        <span class="text-primary fw-bold">{{ number_format($product->price) }}₫</span>
                                    @endif
                                </div>
                                <a href="{{ route('products.show', $product->slug) }}" class="btn btn-outline-primary btn-sm w-100">
                                    Xem chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="text-center mt-4">
                <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg">
                    Xem tất cả sản phẩm
                    <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
/* Hero Banner */
.hero-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    position: relative;
    overflow: hidden;
}

.hero-banner::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/><circle cx="25" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    pointer-events: none;
}

.hero-content {
    position: relative;
    z-index: 1;
}

.hero-icons i {
    animation: bounce 2s infinite;
    animation-delay: calc(var(--i) * 0.2s);
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-10px); }
    60% { transform: translateY(-5px); }
}

/* Category Cards */
.category-card {
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    cursor: pointer;
    border-radius: 20px;
    overflow: hidden;
}

.category-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 20px 40px rgba(0,0,0,0.2) !important;
}

.card-header-gradient {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    position: relative;
    overflow: hidden;
}

.bg-gradient-danger { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%) !important; }
.bg-gradient-success { background: linear-gradient(135deg, #51cf66 0%, #40c057 100%) !important; }
.bg-gradient-info { background: linear-gradient(135deg, #74c0fc 0%, #339af0 100%) !important; }
.bg-gradient-warning { background: linear-gradient(135deg, #ffd43b 0%, #fab005 100%) !important; }
.bg-gradient-secondary { background: linear-gradient(135deg, #868e96 0%, #495057 100%) !important; }

.icon-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    transition: transform 0.3s ease;
}

.category-card:hover .icon-circle {
    transform: scale(1.1) rotate(10deg);
}

/* Stats Cards */
.stat-card {
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.stat-card:hover {
    border-color: #007bff;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,123,255,0.2);
}

.stat-number {
    font-size: 1.8rem !important;
    line-height: 1;
}

.stat-label {
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Price Range */
.price-range {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 2px solid #dee2e6;
    transition: all 0.3s ease;
}

.category-card:hover .price-range {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.price-text {
    font-size: 1.1rem;
}

/* Buttons */
.btn {
    border-radius: 50px;
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    border: none;
    box-shadow: 0 5px 15px rgba(0,123,255,0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,123,255,0.4);
}

/* Popular Categories */
.bg-light {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
}

/* Product Cards */
.product-card {
    transition: all 0.3s ease;
    border-radius: 15px;
    overflow: hidden;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
}

.badge-sale {
    position: absolute;
    top: 15px;
    right: 15px;
    z-index: 1;
    border-radius: 20px;
    padding: 8px 12px;
    font-weight: 600;
    box-shadow: 0 2px 10px rgba(220,53,69,0.3);
}

/* Empty State */
.text-center i {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 0.6; }
    50% { opacity: 1; }
    100% { opacity: 0.6; }
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-banner {
        margin-bottom: 2rem;
    }
    
    .display-4 {
        font-size: 2rem;
    }
    
    .category-card:hover {
        transform: translateY(-5px) scale(1.01);
    }
    
    .icon-circle {
        width: 60px;
        height: 60px;
    }
    
    .icon-circle i {
        font-size: 2rem !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add click event to category cards
    const categoryCards = document.querySelectorAll('.category-card');
    categoryCards.forEach(card => {
        card.addEventListener('click', function(e) {
            // Don't trigger if clicking on the button
            if (!e.target.closest('.btn')) {
                const link = this.querySelector('a');
                if (link) {
                    // Add loading animation
                    this.style.opacity = '0.7';
                    this.style.transform = 'scale(0.98)';
                    
                    setTimeout(() => {
                        window.location.href = link.href;
                    }, 200);
                }
            }
        });
    });
    
    // Hero icons animation
    const heroIcons = document.querySelectorAll('.hero-icons i');
    heroIcons.forEach((icon, index) => {
        icon.style.setProperty('--i', index);
    });
    
    // Parallax effect for hero
    window.addEventListener('scroll', function() {
        const hero = document.querySelector('.hero-banner');
        if (hero) {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            hero.style.transform = `translateY(${rate}px)`;
        }
    });
    
    // Stats animation on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const statNumbers = entry.target.querySelectorAll('.stat-number');
                statNumbers.forEach(stat => {
                    const finalValue = parseInt(stat.textContent);
                    animateNumber(stat, 0, finalValue, 1000);
                });
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    const categoryCards2 = document.querySelectorAll('.category-card');
    categoryCards2.forEach(card => {
        observer.observe(card);
    });
    
    // Number animation function
    function animateNumber(element, start, end, duration) {
        const startTime = performance.now();
        
        function updateNumber(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            const easeOutQuart = 1 - Math.pow(1 - progress, 4);
            const current = Math.floor(start + (end - start) * easeOutQuart);
            
            element.textContent = current;
            
            if (progress < 1) {
                requestAnimationFrame(updateNumber);
            } else {
                element.textContent = end;
            }
        }
        
        requestAnimationFrame(updateNumber);
    }
    
    // Add ripple effect to buttons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                border-radius: 50%;
                background: rgba(255,255,255,0.3);
                pointer-events: none;
                animation: ripple 0.6s ease-out;
            `;
            
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
    
    // Add CSS for ripple animation
    if (!document.querySelector('#ripple-styles')) {
        const style = document.createElement('style');
        style.id = 'ripple-styles';
        style.textContent = `
            @keyframes ripple {
                0% {
                    transform: scale(0);
                    opacity: 1;
                }
                100% {
                    transform: scale(2);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
});
</script>
@endpush
@endsection
