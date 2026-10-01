<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerShopping
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->canShopAsCustomer()) {
            $message = 'الطلب والعضويات والاشتراكات والمحفظة مخصصة للزبائن فقط.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'ok' => false,
                    'message' => $message,
                ], 403);
            }

            return redirect()->to($user->staffHomeRoute())->with('error', $message);
        }

        return $next($request);
    }
}
