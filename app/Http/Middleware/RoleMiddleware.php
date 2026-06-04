<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();
        
        // Super admin има достъп до всичко
        if ($user->hasRole('super_admin')) {
            return $next($request);
        }
        
        // Проверка за множество роли (разделени с |)
        $roles = explode('|', $role);
        
        foreach ($roles as $r) {
            if ($user->hasRole($r)) {
                return $next($request);
            }
        }
        
        abort(403, 'Нямате права за достъп до този раздел.');
    }
}