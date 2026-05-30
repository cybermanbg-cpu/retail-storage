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
        }
        
        // Ако няма, покажи избор на собственик (за супер администратори)
        if (!session()->has('current_owner_id') && auth()->check()) {
            $owners = Owner::where('is_active', true)->get();
            
            if ($owners->count() === 1) {
                session()->put('current_owner_id', $owners->first()->id);
            } elseif ($owners->count() > 1 && !$request->is('owner/select*')) {
                return redirect()->route('owner.select');
            }
        }
        
        return $next($request);
    }
}