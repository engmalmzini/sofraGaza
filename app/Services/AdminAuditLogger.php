<?php

namespace App\Services;

use App\Models\AdminAuditLog;
use App\Models\User;
use App\Support\AdminAccess;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuditLogger
{
    public function record(
        User $actor,
        string $action,
        string $description,
        ?Model $subject = null,
        array $properties = [],
        ?Request $request = null,
    ): AdminAuditLog {
        $request ??= request();

        return AdminAuditLog::query()->create([
            'user_id' => $actor->id,
            'actor_name' => $actor->name,
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'properties' => $properties ?: null,
            'ip_address' => $request?->ip(),
            'url' => $request ? substr($request->fullUrl(), 0, 500) : null,
            'method' => $request?->method(),
        ]);
    }

    public function fromRequest(Request $request, Response $response): ?AdminAuditLog
    {
        $actor = $request->user();
        if (! $actor?->isAdmin()) {
            return null;
        }

        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return null;
        }

        if ($response->getStatusCode() >= 400) {
            return null;
        }

        if ($request->hasSession() && ($request->session()->has('errors') || $request->session()->has('error'))) {
            return null;
        }

        $routeName = $request->route()?->getName();
        if (AdminAccess::skipAuditRoute($routeName)) {
            return null;
        }

        $subject = $this->subjectFromRequest($request);
        $label = AdminAccess::actionLabel($routeName, $request->method());
        $description = $this->humanDescription($actor, $label, $subject, $request);

        return $this->record(
            $actor,
            $routeName ?: strtolower($request->method()),
            $description,
            $subject,
            $this->safeInput($request),
            $request,
        );
    }

    private function humanDescription(User $actor, string $label, ?Model $subject, Request $request): string
    {
        $target = $this->subjectLabel($subject, $request);

        if ($target !== '') {
            return "{$actor->name} — {$label}: {$target}";
        }

        return "{$actor->name} — {$label}";
    }

    private function subjectFromRequest(Request $request): ?Model
    {
        $route = $request->route();
        if (! $route) {
            return null;
        }

        foreach ($route->parameters() as $value) {
            if ($value instanceof Model) {
                return $value;
            }
        }

        return null;
    }

    private function subjectLabel(?Model $subject, Request $request): string
    {
        if ($subject instanceof User) {
            return $subject->name.' ('.$subject->phone.')';
        }

        if ($subject && isset($subject->name)) {
            return (string) $subject->name;
        }

        if ($subject && isset($subject->id)) {
            return '#'.$subject->id;
        }

        foreach (['name', 'reason'] as $key) {
            $value = trim((string) $request->input($key, ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function safeInput(Request $request): array
    {
        $hidden = [
            'password', 'password_confirmation', '_token', '_method',
            'receipt', 'photo', 'image', 'logo', 'qr',
        ];

        $input = $request->except($hidden);
        foreach ($input as $key => $value) {
            if (is_string($value) && strlen($value) > 240) {
                $input[$key] = mb_substr($value, 0, 240).'…';
            }
        }

        return $input;
    }
}
