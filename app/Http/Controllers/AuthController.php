<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class AuthController extends Controller
{
    public function getLogin()
    {
        $title = "Login";
        $subtitle = "Arka HERO";

        return view('auth.login', compact('title', 'subtitle'));
    }

    public function postLogin(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|ends_with:@arka.co.id',
            'password' => 'required|min:5',
        ], [
            'email.required' => 'Email is required',
            'password.required' => 'Password is required'
        ]);
        if (Auth::attempt(['email' => $validatedData['email'], 'password' => $validatedData['password'], 'user_status' => 1])) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        } else {
            return back()->with('errors', 'Login failed!');
        }
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login')->with('toast_success', 'You have been successfully logged out');
    }
}
