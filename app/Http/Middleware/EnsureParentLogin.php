<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureParentLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('parent_account_id')) {
            return redirect()->guest(route('parent.login'));
        }

        return $next($request);
    }
}
