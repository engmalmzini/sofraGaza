<?php

namespace App\Http\Middleware;

use App\Support\AdminAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user?->isAdmin()) {
            abort(403, 'هذه الصفحة مخصصة للإدارة فقط.');
        }

        if (! $user->isActiveAdmin()) {
            abort(403, 'تم إيقاف صلاحية هذا الحساب الإداري.');
        }

        $module = AdminAccess::permissionForRoute($request->route()?->getName());
        if (! $user->canAccessAdmin($module)) {
            abort(403, 'ما عندك صلاحية لهالجزء. تواصل مع المدير الأعلى.');
        }

        return $next($request);
    }
}
