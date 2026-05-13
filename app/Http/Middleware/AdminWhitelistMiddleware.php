<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminWhitelistMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, \Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $allowedEmails = explode(',', config('auth.admin_emails', ''));
        $allowedEmails = array_map('trim', $allowedEmails);

        if (!in_array(Auth::user()->email, $allowedEmails)) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Akses ditolak. Email Anda tidak memiliki izin akses.');
        }

        return $next($request);
    }
}
