<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * تتأكد أن المستخدم مسجّل دخول ودوره "أدمن".
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'admin') {
            abort(403, 'هذه الصفحة مخصصة لإدارة المنصة فقط.');
        }

        return $next($request);
    }
}
