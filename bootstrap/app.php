<?php

use App\Http\Middleware\EnsurePartnerListingAccess;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsCourier;
use App\Http\Middleware\EnsureUserIsRestaurantOwner;
use App\Services\ExpiryNoticeService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'partner' => EnsureUserIsRestaurantOwner::class,
            'partner.listing' => EnsurePartnerListingAccess::class,
            'courier' => EnsureUserIsCourier::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'logout',
        ]);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(function () {
            $user = auth()->user();

            if ($user?->isAdmin()) {
                return route('admin.dashboard');
            }

            if ($user?->isRestaurantOwner()) {
                return $user->partnerPanelRoute();
            }

            if ($user?->isCourier()) {
                return route('courier.dashboard');
            }

            return route('home');
        });
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->call(fn () => app(ExpiryNoticeService::class)->dispatch())->daily();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->is('logout') || $request->routeIs('logout')) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('home');
            }

            return redirect()->back()->with('error', 'انتهت صلاحية الصفحة. أعد المحاولة.');
        });
    })->create();
