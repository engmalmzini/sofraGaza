<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsCourier
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isCourier()) {
            abort(403, 'هذه الصفحة مخصصة لمندوبي التوصيل.');
        }

        return $next($request);
    }
}
