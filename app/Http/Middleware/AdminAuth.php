<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (empty($roles)) $roles = ['superadmin'];

        foreach ($roles as $role) {
            if (Auth::check() && Auth::user()->level === $role) {
                return $next($request);
            } elseif (Auth::check() && Auth::user()->level === null) {
                return redirect('login');
            }
        }
        return response()->view('errors.403', ['title' => '403 Error']);
    }

    // public function handle(Request $request, Closure $next)
    // {
    //     if (Auth::check() && Auth::user()->level == 'superadmin') {
    //         return $next($request);
    //     } elseif (Auth::check() && Auth::user()->level == 'admin') {
    //         return $next($request);
    //     } elseif (Auth::check() && Auth::user()->level == 'user') {
    //         return $next($request);
    //     } else {
    //         return redirect()->route('login');
    //     }
    // }
}
