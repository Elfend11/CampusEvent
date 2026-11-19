<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * @param  string  ...$roles  Role values such as SUPER_ADMIN.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(401, 'Unauthenticated.');
        }

        $allowed = array_map(
            fn (string $role) => UserRole::from($role),
            $roles,
        );

        if (! $user->hasAnyRole($allowed)) {
            abort(403, 'Anda tidak memiliki akses untuk tindakan ini.');
        }

        return $next($request);
    }
}
