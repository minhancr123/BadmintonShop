@extends('layouts.app')

@section('title', 'Đăng ký - Badminton Shop')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-header text-center">
                    <h3 class="mb-0">Đăng ký tài khoản</h3>
                    <p class="text-muted mt-2">Tạo tài khoản để bắt đầu mua sắm!</p>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" 
                                   name="name" value="{{ old('name') }}" required autocomplete="name" autofocus 
                                   placeholder="Nhập họ và tên đầy đủ">
                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                                   name="email" value="{{ old('email') }}" required autocomplete="email" 
                                   placeholder="Nhập địa chỉ email">
                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div class="mb-3">
                            <label for="phone" class="form-label">Số điện thoại</label>
                            <input id="phone" type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                   name="phone" value="{{ old('phone') }}" 
                                   placeholder="Nhập số điện thoại (tùy chọn)">
                            @error('phone')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                                       name="password" required autocomplete="new-password" 
                                       placeholder="Tối thiểu 8 ký tự">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                    <i class="fas fa-eye" id="togglePasswordIcon"></i>
                                </button>
                            </div>
                            <div class="form-text">Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ và số.</div>
                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-3">
                            <label for="password-confirm" class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input id="password-confirm" type="password" class="form-control" 
                                       name="password_confirmation" required autocomplete="new-password" 
                                       placeholder="Nhập lại mật khẩu">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password-confirm')">
                                    <i class="fas fa-eye" id="toggleConfirmPasswordIcon"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="mb-3">
                            <label for="address" class="form-label">Địa chỉ</label>
                            <textarea id="address" class="form-control @error('address') is-invalid @enderror" 
                                      name="address" rows="2" placeholder="Nhập địa chỉ (tùy chọn)">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" 
                                       name="terms" id="terms" required {{ old('terms') ? 'checked' : '' }}>
                                <label class="form-check-label" for="terms">
                                    Tôi đồng ý với <a href="{{ route('terms') }}" target="_blank" class="text-decoration-none">Điều khoản sử dụng</a> 
                                    và <a href="{{ route('privacy') }}" target="_blank" class="text-decoration-none">Chính sách bảo mật</a> 
                                    <span class="text-danger">*</span>
                                </label>
                                @error('terms')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Newsletter -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="newsletter" id="newsletter" {{ old('newsletter') ? 'checked' : '' }}>
                                <label class="form-check-label" for="newsletter">
                                    Đăng ký nhận thông tin khuyến mãi và sản phẩm mới
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-user-plus"></i> Đăng ký tài khoản
                            </button>
                        </div>

                        <!-- Links -->
                        <div class="text-center mt-4">
                            <span class="text-muted">Đã có tài khoản?</span>
                            <a href="{{ route('login') }}" class="text-decoration-none">Đăng nhập ngay</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Benefits -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="text-center mb-3">Lợi ích khi đăng ký tài khoản</h6>
                    <div class="row text-center">
                        <div class="col-md-4 mb-2">
                            <i class="fas fa-shipping-fast text-primary"></i>
                            <small class="d-block mt-1">Giao hàng nhanh</small>
                        </div>
                        <div class="col-md-4 mb-2">
                            <i class="fas fa-gift text-success"></i>
                            <small class="d-block mt-1">Ưu đãi độc quyền</small>
                        </div>
                        <div class="col-md-4 mb-2">
                            <i class="fas fa-history text-info"></i>
                            <small class="d-block mt-1">Theo dõi đơn hàng</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Access -->
            <div class="text-center mt-3">
                <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-home"></i> Về trang chủ
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function togglePassword(fieldId) {
    const passwordField = document.getElementById(fieldId);
    const toggleIcon = fieldId === 'password' ? 
        document.getElementById('togglePasswordIcon') : 
        document.getElementById('toggleConfirmPasswordIcon');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

// Password strength indicator
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthText = document.querySelector('.form-text');
    
    if (password.length === 0) {
        strengthText.textContent = 'Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ và số.';
        strengthText.className = 'form-text';
    } else if (password.length < 8) {
        strengthText.textContent = 'Mật khẩu quá ngắn.';
        strengthText.className = 'form-text text-danger';
    } else if (!/(?=.*[a-zA-Z])(?=.*[0-9])/.test(password)) {
        strengthText.textContent = 'Mật khẩu phải có cả chữ và số.';
        strengthText.className = 'form-text text-warning';
    } else {
        strengthText.textContent = 'Mật khẩu mạnh.';
        strengthText.className = 'form-text text-success';
    }
});

// Confirm password validation
document.getElementById('password-confirm').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (confirmPassword.length > 0) {
        if (password === confirmPassword) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
        }
    } else {
        this.classList.remove('is-invalid', 'is-valid');
    }
});
</script>
@endpush
@endsection
