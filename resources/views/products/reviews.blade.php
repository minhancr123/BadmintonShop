@extends('layouts.app')

@section('title', 'Đánh giá sản phẩm - ' . $product->name)

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Sản phẩm</a></li>
            <li class="breadcrumb-item"><a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a></li>
            <li class="breadcrumb-item active">Đánh giá</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-3">
            <!-- Product Info -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    <img src="{{ $product->image ? Storage::url($product->image) : '/images/no-image.png' }}" 
                         alt="{{ $product->name }}" class="img-fluid mb-3" style="max-height: 200px;">
                    <h6>{{ $product->name }}</h6>
                    <p class="text-muted mb-0">{{ $product->brand }}</p>
                </div>
            </div>

            <!-- Rating Summary -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Tổng quan đánh giá</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h2 class="display-5 fw-bold text-primary">{{ $reviewStats['average'] }}</h2>
                        <div class="stars mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $reviewStats['average'])
                                    <span class="text-warning fs-5">★</span>
                                @else
                                    <span class="text-muted fs-5">☆</span>
                                @endif
                            @endfor
                        </div>
                        <p class="text-muted mb-0">{{ $reviewStats['total'] }} đánh giá</p>
                    </div>
                    
                    @for($i = 5; $i >= 1; $i--)
                        <div class="d-flex align-items-center mb-2">
                            <span class="me-2">{{ $i }}★</span>
                            <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                <div class="progress-bar bg-warning" style="width: {{ $reviewStats['percentages'][$i] }}%"></div>
                            </div>
                            <small class="text-muted">{{ $reviewStats['distribution'][$i] }}</small>
                        </div>
                    @endfor
                </div>
            </div>

            <!-- Filter by Rating -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Lọc theo đánh giá</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('products.reviews', $product) }}" 
                           class="list-group-item list-group-item-action {{ !$rating ? 'active' : '' }}">
                            Tất cả đánh giá
                        </a>
                        @for($i = 5; $i >= 1; $i--)
                            <a href="{{ route('products.reviews', $product) }}?rating={{ $i }}" 
                               class="list-group-item list-group-item-action {{ $rating == $i ? 'active' : '' }}">
                                {{ $i }} sao ({{ $reviewStats['distribution'][$i] }})
                            </a>
                        @endfor
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <!-- Filter and Sort -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>Đánh giá sản phẩm ({{ $reviews->total() }})</h4>
                
                <div class="d-flex gap-2">
                    <select class="form-select" onchange="location.href=this.value">
                        <option value="{{ route('products.reviews', $product) }}?{{ http_build_query(array_merge(request()->except('sort'), ['sort' => 'newest'])) }}" 
                                {{ $sortBy == 'newest' ? 'selected' : '' }}>Mới nhất</option>
                        <option value="{{ route('products.reviews', $product) }}?{{ http_build_query(array_merge(request()->except('sort'), ['sort' => 'oldest'])) }}" 
                                {{ $sortBy == 'oldest' ? 'selected' : '' }}>Cũ nhất</option>
                        <option value="{{ route('products.reviews', $product) }}?{{ http_build_query(array_merge(request()->except('sort'), ['sort' => 'helpful'])) }}" 
                                {{ $sortBy == 'helpful' ? 'selected' : '' }}>Hữu ích nhất</option>
                        <option value="{{ route('products.reviews', $product) }}?{{ http_build_query(array_merge(request()->except('sort'), ['sort' => 'rating_high'])) }}" 
                                {{ $sortBy == 'rating_high' ? 'selected' : '' }}>Đánh giá cao</option>
                        <option value="{{ route('products.reviews', $product) }}?{{ http_build_query(array_merge(request()->except('sort'), ['sort' => 'rating_low'])) }}" 
                                {{ $sortBy == 'rating_low' ? 'selected' : '' }}>Đánh giá thấp</option>
                    </select>
                    
                    @if(auth()->check())
                        <a href="{{ route('reviews.create', $product) }}" class="btn btn-primary">
                            <i class="fas fa-star"></i> Viết đánh giá
                        </a>
                    @endif
                </div>
            </div>

            <!-- Reviews List -->
            @if($reviews->count() > 0)
                <div class="reviews-list">
                    @foreach($reviews as $review)
                        <div class="card mb-3">
                            <div class="card-body">
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
                                            <span class="badge bg-success">✓ Đã mua sản phẩm</span>
                                        @endif
                                    </div>
                                    <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                                </div>
                                
                                @if($review->title)
                                    <h6 class="fw-bold mb-2">{{ $review->title }}</h6>
                                @endif
                                
                                @if($review->comment)
                                    <p class="mb-3">{{ $review->comment }}</p>
                                @endif
                                
                                @if($review->pros && count($review->pros) > 0)
                                    <div class="mb-2">
                                        <small class="text-success fw-bold">Ưu điểm:</small>
                                        <ul class="list-unstyled ms-3 mb-2">
                                            @foreach($review->pros as $pro)
                                                <li><small class="text-success">+ {{ $pro }}</small></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                
                                @if($review->cons && count($review->cons) > 0)
                                    <div class="mb-2">
                                        <small class="text-danger fw-bold">Nhược điểm:</small>
                                        <ul class="list-unstyled ms-3 mb-2">
                                            @foreach($review->cons as $con)
                                                <li><small class="text-danger">- {{ $con }}</small></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        @if($review->helpful_count > 0)
                                            <small class="text-muted">{{ $review->helpful_count }} người thấy hữu ích</small>
                                        @endif
                                    </div>
                                    
                                    @if(auth()->check() && auth()->id() !== $review->user_id)
                                        <button class="btn btn-sm btn-outline-secondary helpful-btn" 
                                                data-review-id="{{ $review->id }}">
                                            <i class="fas fa-thumbs-up"></i> Hữu ích
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $reviews->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-star-o text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Chưa có đánh giá nào</h5>
                    <p class="text-muted">
                        @if($rating)
                            Chưa có đánh giá {{ $rating }} sao nào cho sản phẩm này.
                        @else
                            Hãy là người đầu tiên đánh giá sản phẩm này!
                        @endif
                    </p>
                    @if(auth()->check())
                        <a href="{{ route('reviews.create', $product) }}" class="btn btn-primary">Viết đánh giá</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary">Đăng nhập để viết đánh giá</a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle helpful button clicks
    document.querySelectorAll('.helpful-btn').forEach(button => {
        button.addEventListener('click', function() {
            const reviewId = this.dataset.reviewId;
            
            fetch(`/reviews/${reviewId}/helpful`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.innerHTML = `<i class="fas fa-thumbs-up"></i> Hữu ích (${data.helpful_count})`;
                    this.disabled = true;
                    this.classList.add('disabled');
                } else {
                    alert(data.error || 'Có lỗi xảy ra');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi thực hiện thao tác');
            });
        });
    });
});
</script>
@endsection
