@extends('layouts.app')

@section('title', $category->name . ' - Badminton Shop')

@section('content')
<div class="container">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">Danh mục</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $category->name }}</li>
        </ol>
    </nav>

    <!-- Category Header -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="category-header bg-light rounded p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="mb-2">{{ $category->name }}</h1>
                        @if($category->description)
                            <p class="text-muted mb-3">{{ $category->description }}</p>
                        @endif
                        <div class="category-stats">
                            <span class="badge bg-primary me-2">{{ $products->total() }} sản phẩm</span>
                            @if($category->products->where('quantity', '>', 0)->count() > 0)
                                <span class="badge bg-success">{{ $category->products->where('quantity', '>', 0)->count() }} có sẵn</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        @php
                            $iconClass = 'fas fa-tag'; // Default icon
                            $iconColor = 'text-primary';
                            
                            // Set specific icons based on category name
                            $categoryName = strtolower($category->name);
                            if (str_contains($categoryName, 'vợt') || str_contains($categoryName, 'racket')) {
                                $iconClass = 'fas fa-baseball-bat';
                                $iconColor = 'text-danger';
                            } elseif (str_contains($categoryName, 'giày') || str_contains($categoryName, 'shoes')) {
                                $iconClass = 'fas fa-shoe-prints';
                                $iconColor = 'text-success';
                            } elseif (str_contains($categoryName, 'áo') || str_contains($categoryName, 'shirt') || str_contains($categoryName, 'clothing')) {
                                $iconClass = 'fas fa-tshirt';
                                $iconColor = 'text-info';
                            } elseif (str_contains($categoryName, 'túi') || str_contains($categoryName, 'balo') || str_contains($categoryName, 'bag')) {
                                $iconClass = 'fas fa-bag-shopping';
                                $iconColor = 'text-warning';
                            } elseif (str_contains($categoryName, 'phụ kiện') || str_contains($categoryName, 'accessories')) {
                                $iconClass = 'fas fa-tools';
                                $iconColor = 'text-secondary';
                            }
                        @endphp
                        <i class="{{ $iconClass }} {{ $iconColor }}" style="font-size: 5rem; opacity: 0.7;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Bộ lọc sản phẩm</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('categories.show', $category->slug) }}" id="filterForm">
                        <!-- Search -->
                        <div class="mb-3">
                            <label for="search" class="form-label">Tìm kiếm</label>
                            <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Tìm sản phẩm...">
                        </div>

                        <!-- Price Range -->
                        <div class="mb-3">
                            <label class="form-label">Khoảng giá</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="number" class="form-control form-control-sm" name="min_price" placeholder="Từ" value="{{ request('min_price') }}">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control form-control-sm" name="max_price" placeholder="Đến" value="{{ request('max_price') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Brand Filter -->
                        @php
                            $categoryBrands = $category->products->where('is_active', true)->whereNotNull('brand')->pluck('brand')->unique();
                        @endphp
                        @if($categoryBrands->count() > 0)
                        <div class="mb-3">
                            <label for="brand" class="form-label">Thương hiệu</label>
                            <select class="form-select form-select-sm" id="brand" name="brand">
                                <option value="">Tất cả thương hiệu</option>
                                @foreach($categoryBrands as $brand)
                                    <option value="{{ $brand }}" {{ request('brand') == $brand ? 'selected' : '' }}>
                                        {{ $brand }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <!-- On Sale Filter -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="on_sale" name="on_sale" value="1" {{ request('on_sale') ? 'checked' : '' }}>
                                <label class="form-check-label" for="on_sale">
                                    Chỉ sản phẩm khuyến mãi
                                </label>
                            </div>
                        </div>

                        <!-- Sort -->
                        <div class="mb-3">
                            <label for="sort" class="form-label">Sắp xếp theo</label>
                            <select class="form-select form-select-sm" id="sort" name="sort">
                                <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Tên A-Z</option>
                                <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Tên Z-A</option>
                                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Giá thấp đến cao</option>
                                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Giá cao đến thấp</option>
                                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Mới nhất</option>
                                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Cũ nhất</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-filter"></i> Áp dụng bộ lọc
                            </button>
                            <a href="{{ route('categories.show', $category->slug) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times"></i> Xóa bộ lọc
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="col-md-9">
            <!-- Sort and View Options -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <p class="mb-0 text-muted">Hiển thị {{ $products->count() }} trong {{ $products->total() }} sản phẩm</p>
                </div>
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="view" id="gridView" checked>
                    <label class="btn btn-outline-primary btn-sm" for="gridView">
                        <i class="fas fa-th"></i>
                    </label>
                    <input type="radio" class="btn-check" name="view" id="listView">
                    <label class="btn btn-outline-primary btn-sm" for="listView">
                        <i class="fas fa-list"></i>
                    </label>
                </div>
            </div>

            <!-- Products -->
            <div id="products-grid" class="row">
                @forelse($products as $product)
                    <div class="col-lg-4 col-md-6 mb-4 product-item">
                        <div class="card product-card h-100 border-0 shadow-sm">
                            @if($product->is_on_sale)
                                <span class="badge bg-danger badge-sale">-{{ $product->discount_percentage }}%</span>
                            @endif
                            @if($product->quantity <= 0)
                                <span class="badge bg-secondary" style="position: absolute; top: 10px; left: 10px;">Hết hàng</span>
                            @endif
                            <img src="{{ $product->image ? asset('storage/' . $product->image) : 'https://via.placeholder.com/300x200?text=No+Image' }}" 
                                 class="card-img-top" alt="{{ $product->name }}" style="height: 200px; object-fit: cover;">
                            <div class="card-body d-flex flex-column">
                                <h6 class="card-title">{{ $product->name }}</h6>
                                @if($product->brand)
                                    <small class="text-muted mb-2">{{ $product->brand }}</small>
                                @endif
                                <p class="card-text text-muted small flex-grow-1">{{ Str::limit($product->description, 60) }}</p>
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            @if($product->is_on_sale)
                                                <span class="text-danger fw-bold">{{ number_format($product->sale_price) }}₫</span><br>
                                                <small class="text-decoration-line-through text-muted">{{ number_format($product->price) }}₫</small>
                                            @else
                                                <span class="text-primary fw-bold">{{ number_format($product->price) }}₫</span>
                                            @endif
                                        </div>
                                        <small class="text-muted">Còn {{ $product->quantity }}</small>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('products.show', $product->slug) }}" class="btn btn-outline-primary btn-sm">Xem chi tiết</a>
                                        @auth
                                            @if($product->quantity > 0)
                                                <form action="{{ route('cart.add', $product) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="quantity" value="1">
                                                    <button type="submit" class="btn btn-success btn-sm w-100">
                                                        <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
                                                    </button>
                                                </form>
                                            @else
                                                <button class="btn btn-secondary btn-sm w-100" disabled>Hết hàng</button>
                                            @endif
                                        @else
                                            <a href="{{ route('login') }}" class="btn btn-success btn-sm">
                                                <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
                                            </a>
                                        @endauth
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h4>Không tìm thấy sản phẩm nào</h4>
                            <p class="text-muted mb-4">Danh mục này hiện chưa có sản phẩm hoặc không có sản phẩm phù hợp với bộ lọc của bạn.</p>
                            <div>
                                <a href="{{ route('categories.show', $category->slug) }}" class="btn btn-primary me-2">Xóa bộ lọc</a>
                                <a href="{{ route('products.index') }}" class="btn btn-outline-primary">Xem tất cả sản phẩm</a>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($products->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $products->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Related Categories -->
    @php
        $relatedCategories = \App\Models\Category::active()
                                                ->where('id', '!=', $category->id)
                                                ->withCount(['products' => function ($query) {
                                                    $query->active();
                                                }])
                                                ->having('products_count', '>', 0)
                                                ->limit(3)
                                                ->get();
    @endphp
    
    @if($relatedCategories->count() > 0)
    <div class="row mt-5">
        <div class="col-12">
            <hr class="my-4">
            <h4 class="mb-4">Danh mục khác</h4>
            <div class="row">
                @foreach($relatedCategories as $relatedCategory)
                <div class="col-md-4 mb-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-tag text-primary mb-3" style="font-size: 2rem;"></i>
                            <h6>{{ $relatedCategory->name }}</h6>
                            <p class="text-muted small">{{ $relatedCategory->products_count }} sản phẩm</p>
                            <a href="{{ route('categories.show', $relatedCategory->slug) }}" class="btn btn-outline-primary btn-sm">
                                Khám phá
                            </a>
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
document.addEventListener('DOMContentLoaded', function() {
    // Remove auto-submit functionality - users must click "Apply" button
    console.log('Category filter form initialized - manual submit only');

    // View toggle functionality
    const gridView = document.getElementById('gridView');
    const listView = document.getElementById('listView');
    const productsGrid = document.getElementById('products-grid');

    listView.addEventListener('change', function() {
        if (this.checked) {
            productsGrid.classList.remove('row');
            const items = productsGrid.querySelectorAll('.product-item');
            items.forEach(item => {
                item.className = 'col-12 mb-3 product-item';
                const card = item.querySelector('.card');
                card.classList.add('flex-row');
                const img = item.querySelector('.card-img-top');
                img.style.width = '200px';
                img.style.height = '150px';
            });
        }
    });

    gridView.addEventListener('change', function() {
        if (this.checked) {
            productsGrid.classList.add('row');
            const items = productsGrid.querySelectorAll('.product-item');
            items.forEach(item => {
                item.className = 'col-lg-4 col-md-6 mb-4 product-item';
                const card = item.querySelector('.card');
                card.classList.remove('flex-row');
                const img = item.querySelector('.card-img-top');
                img.style.width = '100%';
                img.style.height = '200px';
            });
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.category-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.product-card {
    transition: transform 0.3s ease;
}

.product-card:hover {
    transform: translateY(-3px);
}

.badge-sale {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 1;
}
</style>
@endpush
@endsection
