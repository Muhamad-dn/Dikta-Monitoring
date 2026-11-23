<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $rawInput = trim($request->input('username'));
        $cleanInput = strtolower(str_replace(' ', '', $rawInput));
        $password = $request->input('password');
        $remember = $request->filled('remember');

        // Cari user yang cocok (case-insensitive & toleran format) berdasarkan:
        // 1. Username (case-insensitive & tanpa spasi)
        // 2. Email (case-insensitive)
        // 3. No. HP / WhatsApp (format asli maupun digit murni)
        // 4. Nama lengkap (case-insensitive)
        $user = User::where(function ($q) use ($rawInput, $cleanInput) {
            $q->whereRaw('LOWER(username) = ?', [$cleanInput])
              ->orWhereRaw('LOWER(username) = ?', [strtolower($rawInput)])
              ->orWhereRaw('LOWER(email) = ?', [strtolower($rawInput)])
              ->orWhere('phone', $rawInput);

            $digitsOnly = preg_replace('/[^0-9]/', '', $rawInput);
            if (!empty($digitsOnly) && strlen($digitsOnly) >= 7) {
                $trimmedLeadingZero = ltrim($digitsOnly, '0');
                $q->orWhere('phone', 'like', "%{$digitsOnly}%")
                  ->orWhere('phone', 'like', "%{$trimmedLeadingZero}%");
            }

            $q->orWhereRaw('LOWER(name) = ?', [strtolower($rawInput)]);
        })->first();

        if ($user && Hash::check($password, $user->password)) {
            if ($user->status !== 'active') {
                return back()->withErrors([
                    'username' => 'Akun Anda dinonaktifkan. Silakan hubungi Administrator.',
                ])->withInput($request->only('username'));
            }

            Auth::login($user, $remember);
            $request->session()->regenerate();

            return $this->redirectBasedOnRole($user);
        }

        return back()->withErrors([
            'username' => 'Username, No. HP/Email, atau kata sandi tidak sesuai.',
        ])->withInput($request->only('username'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }

    protected function redirectBasedOnRole($user)
    {
        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'koordinator' => redirect()->route('coordinator.dashboard'),
            'teknisi' => redirect()->route('technician.dashboard'),
            default => redirect()->route('login'),
        };
    }
}
