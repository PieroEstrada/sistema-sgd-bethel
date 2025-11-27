<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\RolUsuario;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión.');
        }

        $user = Auth::user();
        $userRole = RolUsuario::from($user->rol);

        // Si no se especifican roles, permitir acceso a usuarios autenticados
        if (empty($roles)) {
            return $next($request);
        }

        // Verificar si el usuario tiene uno de los roles requeridos
        foreach ($roles as $role) {
            if ($userRole->value === $role) {
                return $next($request);
            }
        }

        // Si llegamos aquí, el usuario no tiene permisos
        return back()->with('error', 'No tienes permisos para acceder a esta sección.')
                    ->withInput();
    }
}