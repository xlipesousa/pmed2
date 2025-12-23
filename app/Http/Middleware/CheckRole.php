<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    public function handle(Request $request, Closure $next, $role)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();
        
        // Administradores podem acessar qualquer área
        if ($user->isAdmin()) {
            return $next($request);
        }
        
        // Verificar se o usuário tem o papel específico
        if (!$user->hasRole($role)) {
            return redirect()->route('dashboard')->with('error', 'Você não tem permissão para acessar esta área.');
        }

        return $next($request);
    }
}