<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthService;
use App\Services\AuthWebService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // this controller for dashboard auth web
    // this controller work in login logout for admin
    protected $authService;

    public function __construct(AuthWebService $authService)
    {
        $this->authService = $authService;
    }

    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('dashboard.home');
        }
        return view('dashboard.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email_or_phone' => 'required',
            'password' => 'required',
        ]);

        try {
            $this->authService->loginUser($request->only(['email_or_phone', 'password']), 'admin', 'admin');

            return redirect()->intended('dashboard');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->onlyInput('email_or_phone');
        }
    }
    public function logout(Request $request)
    {
        $guard = $this->authService->logout($request, 'admin');

        if ($guard) {
            // Fallback if no guard was authenticated
            return redirect('/dashboard/login');
        }
    }
}
