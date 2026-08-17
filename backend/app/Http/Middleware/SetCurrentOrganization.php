<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentOrganization
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $organizationId = (int) $request->header('X-Organization-Id');

        if (!$user || $organizationId < 1) {
            return response()->json(['message' => 'Organization context is required.'], 403);
        }

        if (!$user->organizations()->whereKey($organizationId)->exists()) {
            return response()->json(['message' => 'Organization access denied.'], 403);
        }

        $request->attributes->set('organization_id', $organizationId);

        return $next($request);
    }
}
