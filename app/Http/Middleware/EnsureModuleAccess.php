<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleAccess
{
    /**
     * Resolve which module the current route belongs to.
     */
    protected function moduleForRoute(Request $request): ?string
    {
        $name = $request->route()?->getName();

        if (! $name) {
            return null;
        }

        foreach (array_merge(config('rbac.modules', []), config('rbac.admin_modules', [])) as $key => $definition) {
            foreach ($definition['routes'] ?? [] as $pattern) {
                if (str_ends_with($pattern, '*')) {
                    $prefix = rtrim($pattern, '*');
                    if ($name === $prefix || str_starts_with($name, $prefix)) {
                        return $key;
                    }
                } elseif ($name === $pattern) {
                    return $key;
                }
            }
        }

        return null;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Authentication required.');
        }

        $module = $this->moduleForRoute($request);

        // Routes with no mapped module (e.g. dashboard health, logout)
        // are permitted for any authenticated user.
        if ($module === null) {
            return $next($request);
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        if (! $user->canModule($module)) {
            $label = config("rbac.modules.$module.label")
                ?? config("rbac.admin_modules.$module.label")
                ?? ucfirst($module);

            abort(403, "You do not have access to the {$label} module. Contact an administrator.");
        }

        return $next($request);
    }
}
