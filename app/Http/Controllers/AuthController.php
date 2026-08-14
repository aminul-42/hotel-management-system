<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Show the single common login page
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect($this->redirectPathForRole(Auth::user()->role));
        }

        return view('auth.login');
    }

    // AJAX login handler
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'This account has been deactivated. Please contact support.',
            ]);
        }

        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'redirect' => $this->redirectPathForRole($user->role),
            'user' => [
                'name' => $user->name,
                'role' => $user->role,
            ],
        ]);
    }

    // AJAX logout handler
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'redirect' => route('home'),
        ]);
    }

    // Central role -> redirect path logic (spec section 3.3)
    private function redirectPathForRole(string $role): string
    {
        return match ($role) {
            'admin' => route('admin.dashboard'),
            'front_desk' => route('frontdesk.dashboard'),
            'customer' => route('home'), // storefront, not a forced dashboard
            default => route('home'),
        };
    }
}