<?php

namespace App\Http\Middleware;

use App\Models\UserMenuAccess;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route as RouteFacade;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks a route when the signed-in staff member has had the corresponding
 * sidebar item switched off, so hiding the link also closes the URL rather
 * than merely removing it from view.
 *
 * Use either as "menu.access" — which infers the item from the current route
 * name via config/menu_access.php — or as "menu.access:calendar" to name the
 * item explicitly.
 */
class EnsureMenuAccess
{
    public function handle(Request $request, Closure $next, ?string $menuKey = null): Response
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        $key = $menuKey ?: UserMenuAccess::keyForRoute($request->route()?->getName());

        // A route not covered by the registry is not governed by this feature.
        if (!$key) {
            return $next($request);
        }

        if ($user->canSeeMenu($key)) {
            return $next($request);
        }

        $message = 'You do not have access to '
            . config('menu_access.items.' . $key . '.label', 'this section') . '.';

        if ($request->expectsJson()) {
            abort(403, $message);
        }

        // Never bounce to the dashboard here: /home redirects straight back to
        // it, so a staff member whose Dashboard is switched off would loop.
        // Send them to the first item they can still open instead.
        $fallback = $this->firstAccessibleRoute($user, $key);

        if (!$fallback) {
            abort(403, $message);
        }

        return redirect()->route($fallback)->with('error', $message);
    }

    /**
     * Name of the first route from another accessible menu item, or null when
     * this staff member has nothing left to land on.
     */
    private function firstAccessibleRoute($user, string $blockedKey): ?string
    {
        foreach (UserMenuAccess::items() as $key => $item) {
            if ($key === $blockedKey || !$user->canSeeMenu($key)) {
                continue;
            }

            foreach ($item['routes'] ?? [] as $routeName) {
                // The dashboard entries are reachable but land via /home, which
                // redirects; skip them so the fallback is always a real page.
                if (in_array($routeName, ['index', 'managementIndex'], true)) {
                    continue;
                }

                if (RouteFacade::has($routeName)) {
                    return $routeName;
                }
            }
        }

        // Dashboard last: safe now, because we only get here when it is allowed.
        if ($blockedKey !== 'dashboard' && $user->canSeeMenu('dashboard') && RouteFacade::has('home')) {
            return 'home';
        }

        return null;
    }
}
