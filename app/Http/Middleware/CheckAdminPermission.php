<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission): Response
    {

        if (!$request->user() || !$request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Bạn không có quyền truy cập vào hệ thống.'], 403);
        }

        if (!$request->user()->hasPermissionTo($permission)) {
            return response()->json(['message' => 'Bạn không có quyền thực hiện hành động này.'], 403);
        }
        return $next($request);
    }
}
