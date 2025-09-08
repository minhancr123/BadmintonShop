@extends('layouts.app')

@section('title', 'Viết đánh giá - ' . $product->name)

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Sản phẩm</a></li>
            <li class="breadcrumb-item"><a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a></li>
            <li class="breadcrumb-item active">Viết đánh giá</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Viết đánh giá cho sản phẩm</h4>
                </div>
                <div class="card-body">
                    <!-- Product Info -->
                    <div class="d-flex mb-4">
                        <img src="{{ $product->image ? Storage::url($product->image) : '/images/no-image.png' }}" 
                             alt="{{ $product->name }}" class="me-3" style="width: 80px; height: 80px; object-fit: cover;">
                        <div>
                            <h5>{{ $product->name }}</h5>
                            <p class="text-muted mb-0">{{ $product->brand }}</p>
                            @if($hasPurchased)
                                <span class="badge bg-success">✓ Đã mua sản phẩm</span>
                            @endif
                        </div>
                    </div>

                    <form action="{{ route('reviews.store', $product) }}" method="POST">
                        @csrf
                        
                        <!-- Rating -->
                        <div class="mb-4">
                            <label class="form-label">Đánh giá của bạn <span class="text-danger">*</span></label>
                            <div class="rating-input">
                                @for($i = 5; $i >= 1; $i--)
                                    <input type="radio" name="rating" value="{{ $i }}" id="star{{ $i }}" 
                                           {{ old('rating') == $i ? 'checked' : '' }}>
                                    <label for="star{{ $i }}" class="star">★</label>
                                @endfor
                            </div>
                            @error('rating')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label">Tiêu đề đánh giá</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title') }}" 
                                   placeholder="Tóm tắt trải nghiệm của bạn">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Comment -->
                        <div class="mb-3">
                            <label for="comment" class="form-label">Nội dung đánh giá</label>
                            <textarea class="form-control @error('comment') is-invalid @enderror" 
                                      id="comment" name="comment" rows="4" 
                                      placeholder="Chia sẻ chi tiết về trải nghiệm sử dụng sản phẩm...">{{ old('comment') }}</textarea>
                            @error('comment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Pros -->
                        <div class="mb-3">
                            <label class="form-label">Ưu điểm</label>
                            <div id="pros-container">
                                @if(old('pros'))
                                    @foreach(old('pros') as $index => $pro)
                                        <div class="input-group mb-2 pros-item">
                                            <input type="text" class="form-control" name="pros[]" value="{{ $pro }}" 
                                                   placeholder="Ưu điểm {{ $index + 1 }}">
                                            <button type="button" class="btn btn-outline-danger remove-item">×</button>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="input-group mb-2 pros-item">
                                        <input type="text" class="form-control" name="pros[]" placeholder="Ưu điểm 1">
                                        <button type="button" class="btn btn-outline-danger remove-item">×</button>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="btn btn-outline-success btn-sm" id="add-pro">+ Thêm ưu điểm</button>
                        </div>

                        <!-- Cons -->
                        <div class="mb-4">
                            <label class="form-label">Nhược điểm</label>
                            <div id="cons-container">
                                @if(old('cons'))
                                    @foreach(old('cons') as $index => $con)
                                        <div class="input-group mb-2 cons-item">
                                            <input type="text" class="form-control" name="cons[]" value="{{ $con }}" 
                                                   placeholder="Nhược điểm {{ $index + 1 }}">
                                            <button type="button" class="btn btn-outline-danger remove-item">×</button>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="input-group mb-2 cons-item">
                                        <input type="text" class="form-control" name="cons[]" placeholder="Nhược điểm 1">
                                        <button type="button" class="btn btn-outline-danger remove-item">×</button>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="btn btn-outline-success btn-sm" id="add-con">+ Thêm nhược điểm</button>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
                            <a href="{{ route('products.show', $product->slug) }}" class="btn btn-secondary">Hủy</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Hướng dẫn viết đánh giá</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Chia sẻ trải nghiệm thực tế
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Đánh giá công bằng và khách quan
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Tránh ngôn từ không phù hợp
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Thêm ưu nhược điểm cụ thể
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.rating-input {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
}

.rating-input input[type="radio"] {
    display: none;
}

.rating-input label.star {
    font-size: 2rem;
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s;
}

.rating-input input[type="radio"]:checked ~ label.star,
.rating-input label.star:hover,
.rating-input label.star:hover ~ label.star {
    color: #ffc107;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add pros
    document.getElementById('add-pro').addEventListener('click', function() {
        const container = document.getElementById('pros-container');
        const count = container.children.length + 1;
        if (count <= 5) {
            const div = document.createElement('div');
            div.className = 'input-group mb-2 pros-item';
            div.innerHTML = `
                <input type="text" class="form-control" name="pros[]" placeholder="Ưu điểm ${count}">
                <button type="button" class="btn btn-outline-danger remove-item">×</button>
            `;
            container.appendChild(div);
        }
    });

    // Add cons
    document.getElementById('add-con').addEventListener('click', function() {
        const container = document.getElementById('cons-container');
        const count = container.children.length + 1;
        if (count <= 5) {
            const div = document.createElement('div');
            div.className = 'input-group mb-2 cons-item';
            div.innerHTML = `
                <input type="text" class="form-control" name="cons[]" placeholder="Nhược điểm ${count}">
                <button type="button" class="btn btn-outline-danger remove-item">×</button>
            `;
            container.appendChild(div);
        }
    });

    // Remove items
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item')) {
            const container = e.target.closest('.input-group').parentNode;
            if (container.children.length > 1) {
                e.target.closest('.input-group').remove();
            }
        }
    });
});
</script>
@endsection
