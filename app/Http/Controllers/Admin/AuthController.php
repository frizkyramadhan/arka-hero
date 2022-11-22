<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function getLogin()
    {
        $title = "Login - Human Capital & Support Information System";
        return view('admin.auth.login', compact('title'));
    }

    public function postLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $validated = auth()->attempt([
            'email' => $request->email,
            'password' => $request->password,
            'is_admin' => 1
        ], $request->password);

        if ($validated) {
            return redirect()->route('dashboard')->with('success', 'Login Successfully');
        } else {
            return redirect()->back()->with('error', 'Invalid Credentials');
        }
    }
}
