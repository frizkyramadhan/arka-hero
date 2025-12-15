<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class RegisterController extends Controller
{
    public function index()
    {
        $title = 'Register';
        $subtitle = 'Arka HERO';

        return view('auth.register', compact('title', 'subtitle'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'username' => 'required|unique:users|alpha_dash|min:3|max:255',
            'email' => 'nullable|email:dns|unique:users|ends_with:@arka.co.id',
            'password' => 'required|min:5',
            'user_status' => 'required',
        ], [
            'name.required' => 'Name is required',
            'username.required' => 'Username is required',
            'username.unique' => 'Username already exists',
            'username.alpha_dash' => 'Username can only contain letters, numbers, dashes and underscores',
            'username.min' => 'Username must be at least 3 characters',
            'email.email' => 'Email must be a valid email address',
            'email.unique' => 'Email already exists',
            'email.ends_with' => 'Email must end with @arka.co.id',
            'password.required' => 'Password is required'
        ]);

        $validatedData['password'] = Hash::make($validatedData['password']);

        $user = User::create($validatedData);

        // Automatically assign 'user' role to newly registered user
        $userRole = Role::firstOrCreate(['name' => 'user']);
        $user->assignRole($userRole);

        return redirect('login')->with('toast_success', 'Registration success! Please contact IT to activate your account.');
    }
}
