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
            'login' => 'required', // Can be email or username
            'password' => 'required|min:5',
        ], [
            'login.required' => 'Email or username is required',
            'password.required' => 'Password is required'
        ]);

        // Determine if login is email or username
        $loginField = filter_var($validatedData['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // If email, validate it ends with @arka.co.id
        if ($loginField === 'email' && !str_ends_with($validatedData['login'], '@arka.co.id')) {
            return back()->with('errors', 'Email must end with @arka.co.id');
        }

        // Attempt authentication
        $credentials = [
            $loginField => $validatedData['login'],
            'password' => $validatedData['password'],
            'user_status' => 1
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        } else {
            return back()->with('errors', 'Login failed!');
        }
    }

    public function apiLogin(Request $request)
    {
        $validatedData = $request->validate([
            'login' => 'required', // Can be email or username
            'password' => 'required|min:5',
        ], [
            'login.required' => 'Email or username is required',
            'password.required' => 'Password is required'
        ]);

        // Determine if login is email or username
        $loginField = filter_var($validatedData['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // If email, validate it ends with @arka.co.id
        if ($loginField === 'email' && !str_ends_with($validatedData['login'], '@arka.co.id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email must end with @arka.co.id'
            ], 422);
        }

        // Attempt authentication
        $credentials = [
            $loginField => $validatedData['login'],
            'password' => $validatedData['password'],
            'user_status' => 1
        ];

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'username' => $user->username,
                    ],
                    'token' => $token
                ]
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ], 401);
        }
    }

    public function apiLogout(Request $request)
    {
        $user = $request->user();
        $tokenId = $user->currentAccessToken()?->id;
        if ($tokenId) {
            $user->tokens()->where('id', $tokenId)->delete();
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ]);
    }

    public function apiUser(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
            ]
        ]);
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login')->with('toast_success', 'You have been successfully logged out');
    }
}
