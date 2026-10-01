<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isAdmin()) {
            abort(403, 'هذه الصفحة مخصصة للإدارة فقط.');
        }

        if (! $request->user()->isActiveAdmin()) {
            abort(403, 'تم إيقاف صلاحية هذا الحساب الإداري.');
        }

        return $next($request);
    }
}
