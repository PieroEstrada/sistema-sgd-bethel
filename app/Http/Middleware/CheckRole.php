<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\RolUsuario;

/**
 * Middleware para verificar roles de usuario
 *
 * Uso en rutas:
 *   ->middleware('role:administrador,coordinador_operaciones')
 *
 * Roles válidos (9):
 *   administrador, sectorista, encargado_ingenieria, encargado_laboratorio,
 *   encargado_logistico, coordinador_operaciones, asistente_contable,
 *   gestor_radiodifusion, visor
 */
class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string ...$roles Lista de roles permitidos
     * @return Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Si no está autenticado, redirigir a login
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Debe iniciar sesión para acceder.');
        }

        $user = Auth::user();

        // Si no se especifican roles, permitir acceso a cualquier usuario autenticado
        if (empty($roles)) {
            return $next($request);
        }

        // Intentar obtener el rol del usuario como enum (soporta string o enum)
        try {
            $userRole = $user->rol instanceof RolUsuario
                ? $user->rol
                : RolUsuario::from((string) $user->rol);
        } catch (\ValueError $e) {
            // Rol inválido en BD (legacy o corrupto) - denegar acceso
            abort(403, 'Rol de usuario inválido. Contacte al administrador.');
        }

        // Verificar si el usuario tiene uno de los roles permitidos
        foreach ($roles as $allowedRole) {
            if ($userRole->value === $allowedRole) {
                return $next($request);
            }
        }

        // Usuario autenticado pero sin permisos - 403 Forbidden
        abort(403, 'No tiene permisos para acceder a esta sección.');
    }
}