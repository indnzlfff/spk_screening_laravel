<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('school_user_id')) {
            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
