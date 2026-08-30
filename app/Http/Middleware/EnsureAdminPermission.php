<?php

namespace App\Http\Middleware;

use App\Models\AdminUser;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user instanceof AdminUser || $user->status !== 'active' || ! $user->can($permission)) {
            return ApiResponse::error('This action is unauthorized.', null, 403);
        }

        return $next($request);
    }
}
