<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePartnerListingAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('partner.subscription.*', 'partner.notifications.*', 'partner.restaurant.*')) {
            return $next($request);
        }

        $restaurant = $request->user()?->ownedRestaurant;

        if ($restaurant?->hasPaidAccess()) {
            return $next($request);
        }

        return redirect()->route('partner.subscription.index');
    }
}
