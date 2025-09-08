@extends('layouts.app')

@section('title', 'Sản phẩm - Badminton Shop')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-3">
            <!-- Filters Sidebar -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Bộ lọc sản phẩm</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('products.index') }}" id="filterForm">
                        <!-- Search -->
                        <div class="mb-3">
                            <label for="search" class="form-label">Tìm kiếm</label>
                            <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Nhập tên sản phẩm...">
                        </div>

                        <!-- Category Filter -->
                        <div class="mb-3">
                            <label for="category" class="form-label">Danh mục</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">Tất cả danh mục</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-3">
                            <label class="form-label">Khoảng giá</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="number" class="form-control" name="min_price" placeholder="Từ" value="{{ request('min_price') }}">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control" name="max_price" placeholder="Đến" value="{{ request('max_price') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Brand Filter -->
                        <div class="mb-3">
                            <label for="brand" class="form-label">Thương hiệu</label>
                            <select class="form-select" id="brand" name="brand">
                                <option value="">Tất cả thương hiệu</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand }}" {{ request('brand') == $brand ? 'selected' : '' }}>
                                        {{ $brand }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

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
                            <select class="form-select" id="sort" name="sort">
                                <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Tên A-Z</option>
                                <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Tên Z-A</option>
                                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Giá thấp đến cao</option>
                                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Giá cao đến thấp</option>
                                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Mới nhất</option>
                                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Cũ nhất</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Áp dụng bộ lọc
                            </button>
                            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Xóa bộ lọc
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1>Sản phẩm</h1>
                    <p class="text-muted mb-0">Tìm thấy {{ $products->total() }} sản phẩm</p>
                </div>
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="view" id="gridView" checked>
                    <label class="btn btn-outline-primary" for="gridView">
                        <i class="fas fa-th"></i>
                    </label>
                    <input type="radio" class="btn-check" name="view" id="listView">
                    <label class="btn btn-outline-primary" for="listView">
                        <i class="fas fa-list"></i>
                    </label>
                </div>
            </div>

            <!-- Products Grid -->
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
                                <h5 class="card-title">{{ $product->name }}</h5>
                                <p class="text-muted small">{{ $product->category->name }}</p>
                                
                                <!-- Rating -->
                                @if($product->reviews_count > 0)
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="stars me-2">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $product->average_rating)
                                                    <small class="text-warning">★</small>
                                                @else
                                                    <small class="text-muted">☆</small>
                                                @endif
                                            @endfor
                                        </div>
                                        <small class="text-muted">({{ $product->reviews_count }})</small>
                                    </div>
                                @endif
                                
                                <p class="card-text text-muted small flex-grow-1">{{ Str::limit($product->description, 80) }}</p>
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            @if($product->is_on_sale)
                                                <span class="text-danger fw-bold fs-5">{{ number_format($product->sale_price) }}₫</span><br>
                                                <small class="text-decoration-line-through text-muted">{{ number_format($product->price) }}₫</small>
                                            @else
                                                <span class="text-primary fw-bold fs-5">{{ number_format($product->price) }}₫</span>
                                            @endif
                                        </div>
                                        <small class="text-muted">Còn {{ $product->quantity }} sản phẩm</small>
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
                            <p class="text-muted">Hãy thử thay đổi bộ lọc tìm kiếm của bạn.</p>
                            <a href="{{ route('products.index') }}" class="btn btn-primary">Xem tất cả sản phẩm</a>
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
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Remove auto-submit functionality - users must click "Apply Filter" button
    console.log('Filter form initialized - manual submit only');

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
@endsection
