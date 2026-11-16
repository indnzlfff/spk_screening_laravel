<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get('school_user_role') !== 'admin') {
            abort(403, 'Fitur ini hanya dapat diakses oleh admin sekolah.');
        }

        return $next($request);
    }
}
