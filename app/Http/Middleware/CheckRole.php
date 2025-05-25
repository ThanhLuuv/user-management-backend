<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle($request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $allRoles = [];
        foreach ($roles as $role) {
            $allRoles = array_merge($allRoles, explode(',', $role));
        }

        if (!$user->hasRole($allRoles)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }


}
