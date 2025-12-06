<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }

    protected function authenticate($request, array $guards)
{
    if (empty($guards)) {
        $guards = [null];
    }

    foreach ($guards as $guard) {
        if (Auth::guard($guard)->check()) {
            // ✳️ Kiểm tra nếu user bị khóa
            if (Auth::guard($guard)->user()->is_blocked) {
                Auth::guard($guard)->logout();

                // Optional: xoá session nếu dùng
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw new AuthenticationException(
                    'Tài khoản của bạn đã bị tạm khóa.',
                    $guards,
                    route('login') // Redirect về login
                );
            }

            return Auth::user();
        }
    }

    throw new AuthenticationException(
        'Unauthenticated.',
        $guards,
        route('login')
    );
}
}
