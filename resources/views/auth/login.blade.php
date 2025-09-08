@extends('layouts.app')

@section('title', 'Đăng nhập - Badminton Shop')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-header text-center">
                    <h3 class="mb-0">Đăng nhập</h3>
                    <p class="text-muted mt-2">Chào mừng bạn quay trở lại!</p>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                                   name="email" value="{{ old('email') }}" required autocomplete="email" autofocus 
                                   placeholder="Nhập địa chỉ email">
                            @error('email')
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
                                       name="password" required autocomplete="current-password" 
                                       placeholder="Nhập mật khẩu">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                    <i class="fas fa-eye" id="togglePasswordIcon"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Remember Me -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    Ghi nhớ đăng nhập
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Đăng nhập
                            </button>
                        </div>

                        <!-- Links -->
                        <div class="text-center mt-4">
                            @if (Route::has('password.request'))
                                <div class="mb-2">
                                    <a class="text-decoration-none" href="{{ route('password.request') }}">
                                        Quên mật khẩu?
                                    </a>
                                </div>
                            @endif
                            <div>
                                <span class="text-muted">Chưa có tài khoản?</span>
                                <a href="{{ route('register') }}" class="text-decoration-none">Đăng ký ngay</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Social Login (Optional) -->
            <div class="card mt-3">
                <div class="card-body text-center">
                    <p class="text-muted mb-3">Hoặc đăng nhập bằng</p>
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary" disabled>
                            <i class="fab fa-facebook"></i> Facebook (Sắp có)
                        </button>
                        <button class="btn btn-outline-danger" disabled>
                            <i class="fab fa-google"></i> Google (Sắp có)
                        </button>
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
function togglePassword() {
    const passwordField = document.getElementById('password');
    const toggleIcon = document.getElementById('togglePasswordIcon');
    
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
</script>
@endpush
@endsection
