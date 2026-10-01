<?php

namespace App\Http\Middleware;

use App\Services\AdminAuditLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RecordAdminAudit
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            app(AdminAuditLogger::class)->fromRequest($request, $response);
        } catch (Throwable) {
            // Never block the original admin action if logging fails.
        }

        return $response;
    }
}
