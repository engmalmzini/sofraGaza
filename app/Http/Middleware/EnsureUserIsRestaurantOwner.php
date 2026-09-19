<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsRestaurantOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->isRestaurantOwner() || ! $user->ownedRestaurant) {
            abort(403, 'هذه الصفحة مخصصة لأصحاب المطاعم المسجّلين.');
        }

        return $next($request);
    }
}
