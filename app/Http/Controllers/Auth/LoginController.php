<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Override the login logic to check if user is blocked.
     */
    protected function credentials(Request $request)
    {
        return $request->only($this->username(), 'password');
    }

    /**
     * Check user status after login attempt.
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        // Check if user is blocked
        $user = Auth::user();
        if ($user && $user->is_blocked) {
            Auth::logout();
            return redirect()->back()
                ->withInput($request->only($this->username()))
                ->withErrors([
                    $this->username() => 'Tài khoản của bạn đã bị tạm khóa liên hệ admin để mở khóa.',
                ]);
        }

        return $this->authenticated($request, $user)
                ?: redirect()->intended($this->redirectPath());
    }
}
