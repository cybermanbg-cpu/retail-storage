<?php

namespace App\Http\Middleware;

use App\Models\Owner;
use Closure;
use Illuminate\Http\Request;

class SetCurrentOwner
{
    public function handle(Request $request, Closure $next)
    {
        // Ако user-а има owner_id
        if (auth()->check() && auth()->user()->owner_id) {
            session()->put('current_owner_id', auth()->user()->owner_id);
            return $next($request);
        }
        
        // Ако няма, покажи избор на собственик (за супер администратори)
        if (!session()->has('current_owner_id') && auth()->check()) {
            $owners = Owner::where('is_active', true)->get();
            
            if ($owners->count() === 1) {
                session()->put('current_owner_id', $owners->first()->id);
            } elseif ($owners->count() > 1 && !$request->is('owner/select*') && !$request->is('shopping-mall*')) {
                // За shopping mall маршрутите не правим пренасочване, взимаме първия собственик
                $firstOwner = $owners->first();
                if ($firstOwner) {
                    session()->put('current_owner_id', $firstOwner->id);
                }
            }
        }
        
        return $next($request);
    }
}