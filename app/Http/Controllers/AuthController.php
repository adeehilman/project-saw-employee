<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function index()
    {
        return view('auth.index', [
            'title' => 'Login'
        ]);
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }
        return back()->with('error', 'Email dan Password tidak cocok.');
    }

    public function logout()
    {
        // Store user info before logout for success message
        $userName = Auth::user()->name;

        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerate();

        // Add success message to session
        request()->session()->flash('logout_success', 'Anda telah berhasil logout. Terima kasih ' . $userName . '!');

        return redirect('/');
    }
}
