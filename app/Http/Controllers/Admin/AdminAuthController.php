<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    /**
     * Handle admin login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'البريد الإلكتروني غير صالح.',
            'password.required' => 'كلمة المرور مطلوبة.',
        ]);

        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $admin = Auth::guard('admin')->user();
            AuditLog::record('admin.login.success', [
                'email' => $credentials['email'],
            ], 'admin', $admin?->id);
            return redirect()->intended(route('admin.dashboard'));
        }

        // Log failed attempt (email only — never the password).
        AuditLog::record('admin.login.failed', [
            'email' => $credentials['email'],
        ], 'admin', null);

        throw ValidationException::withMessages([
            'email' => 'بيانات الاعتماد المدخلة غير صحيحة.',
        ]);
    }

    /**
     * Handle admin logout request.
     */
    public function logout(Request $request)
    {
        $adminId = Auth::guard('admin')->id();
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        AuditLog::record('admin.logout', [], 'admin', $adminId);

        return redirect()->route('admin.login');
    }
}
