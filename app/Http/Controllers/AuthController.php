<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Enums\RolUsuario;

class AuthController extends Controller
{
    /**
     * Mostrar formulario de login
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.login');
    }

    /**
     * Procesar login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ], [
            'email.required' => 'El email es obligatorio',
            'email.email' => 'El email debe ser válido',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres'
        ]);

        // Verificar si el usuario existe y está activo
        $user = User::where('email', $credentials['email'])->first();
        
        if (!$user) {
            return back()->withErrors([
                'email' => 'No existe una cuenta con este email.',
            ])->onlyInput('email');
        }

        if (!$user->activo) {
            return back()->withErrors([
                'email' => 'Tu cuenta está desactivada. Contacta al administrador.',
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            // Actualizar último acceso y registrar login
            $user->update(['ultimo_acceso' => now()]);
            
            // Registrar en auditoría
            $this->registrarAccesoSistema($user, 'LOGIN', $request);
            
            // ✅ REDIRECCIÓN CON NUEVOS ROLES
            $redirectRoute = $this->getRedirectRouteByRole($user->rol->value);
            
            return redirect()->intended($redirectRoute)
                           ->with('success', "¡Bienvenido {$user->name}!");
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no son correctas.',
        ])->onlyInput('email');
    }

    /**
     * Cerrar sesión con auditoría completa
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        $userName = $user->name ?? 'Usuario';
        $userId = $user->id ?? null;
        
        // Registrar logout en auditoría ANTES de cerrar sesión
        if ($userId) {
            $this->registrarAccesoSistema($user, 'LOGOUT', $request);
        }
        
        // Cerrar sesión
        Auth::logout();
        
        // Invalidar sesión completamente
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Limpiar cookies de autenticación si existen
        $response = redirect()->route('login')
                             ->with('success', "¡Hasta luego, {$userName}! Sesión cerrada correctamente.");
        
        // Limpiar cookies de Laravel
        $response->withCookie(cookie()->forget('laravel_session'));
        $response->withCookie(cookie()->forget('remember_web'));
        
        return $response;
    }

    /**
     * Obtener datos del usuario actual para AJAX - ✅ ACTUALIZADO CON NUEVOS ROLES
     */
    public function currentUser()
    {
        if (!Auth::check()) {
            return response()->json(['authenticated' => false]);
        }

        $user = Auth::user();
        
        // ✅ MANEJAR TANTO ROLES ANTIGUOS COMO NUEVOS
        try {
            $userRole = RolUsuario::from($user->rol);
        } catch (\ValueError $e) {
            // Si el rol no existe en el nuevo enum, asignar visor por defecto
            $userRole = RolUsuario::VISOR;
        }
        
        return response()->json([
            'authenticated' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'rol' => $user->rol,
                'telefono' => $user->telefono,
                'sector_asignado' => $user->sector_asignado ?? null,
                'area_especialidad' => $user->area_especialidad ?? null,
                'nivel_acceso' => $user->nivel_acceso ?? 'limitado',
                'ultimo_acceso' => $user->ultimo_acceso?->format('d/m/Y H:i'),
                
                // ✅ PERMISOS ACTUALIZADOS CON NUEVOS ROLES
                'can_modify_all_stations' => $userRole === RolUsuario::ADMINISTRADOR,
                'can_modify_sector_stations' => $userRole === RolUsuario::SECTORISTA,
                'can_only_view_stations' => $userRole === RolUsuario::VISOR,
                
                'can_assign_incidents' => in_array($userRole, [
                    RolUsuario::ADMINISTRADOR, 
                    RolUsuario::SECTORISTA, 
                    RolUsuario::COORDINADOR_OPERACIONES
                ]),
                'can_manage_users' => $userRole === RolUsuario::ADMINISTRADOR,
                'can_close_incidents' => in_array($userRole, [
                    RolUsuario::ADMINISTRADOR, 
                    RolUsuario::COORDINADOR_OPERACIONES,
                    RolUsuario::ENCARGADO_INGENIERIA,
                    RolUsuario::ENCARGADO_LABORATORIO
                ]),
                'can_create_incidents' => !($userRole === RolUsuario::VISOR),
                'can_manage_mtc_procedures' => $userRole->puedeGestionarTramitesMTC(),
                'can_manage_stations' => $userRole->puedeGestionarEstaciones(),
                'is_read_only' => $userRole->esSoloLectura(),
                
                // Información de roles
                'is_admin' => $userRole === RolUsuario::ADMINISTRADOR,
                'is_sectorist' => $userRole === RolUsuario::SECTORISTA,
                'is_engineering' => $userRole === RolUsuario::ENCARGADO_INGENIERIA,
                'is_laboratory' => $userRole === RolUsuario::ENCARGADO_LABORATORIO,
                'is_logistics' => $userRole === RolUsuario::ENCARGADO_LOGISTICO,
                'is_operations' => $userRole === RolUsuario::COORDINADOR_OPERACIONES,
                'is_accounting' => $userRole === RolUsuario::ASISTENTE_CONTABLE,
                'is_broadcasting' => $userRole === RolUsuario::GESTOR_RADIODIFUSION,
                'is_viewer' => $userRole === RolUsuario::VISOR,

                'role_display' => $this->getRoleDisplayName($user->rol),
                'role_badge_class' => $this->getRoleBadgeClass($user->rol)
            ]
        ]);
    }

    /**
     * Forzar logout por seguridad (admin)
     */
    public function forceLogout(Request $request, $userId)
    {
        // Solo administradores pueden forzar logout
        if (Auth::user()->rol !== 'administrador') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $user = User::findOrFail($userId);
        
        // Invalidar todas las sesiones del usuario
        DB::table('sessions')->where('user_id', $userId)->delete();
        
        // Registrar acción
        $this->registrarAccesoSistema($user, 'FORCE_LOGOUT', $request, [
            'forced_by' => Auth::user()->name,
            'reason' => $request->input('reason', 'Logout forzado por administrador')
        ]);

        return response()->json([
            'success' => true,
            'message' => "Sesión de {$user->name} cerrada forzadamente"
        ]);
    }

    /**
     * Obtener lista de usuarios para dropdowns - ✅ ACTUALIZADO
     */
    public function getUsersForDropdown(Request $request)
    {
        $currentUser = Auth::user();
        
        // Manejar roles antiguos y nuevos
        try {
            $currentRole = RolUsuario::from($currentUser->rol);
        } catch (\ValueError $e) {
            $currentRole = RolUsuario::VISOR;
        }
        
        $query = User::where('activo', true);
        
        // Filtrar usuarios según el rol del usuario actual
        if ($currentRole === RolUsuario::SECTORISTA && $currentUser->sector_asignado) {
            $query->where(function($q) use ($currentUser) {
                $q->where('sector_asignado', $currentUser->sector_asignado)
                  ->orWhereNull('sector_asignado')
                  ->orWhere('rol', 'administrador');
            });
        }
        
        // Filtrar por rol si se especifica
        if ($request->filled('rol')) {
            $query->where('rol', $request->rol);
        }
        
        // Filtrar por área si se especifica
        if ($request->filled('area')) {
            $query->where('area_especialidad', $request->area);
        }
        
        // Filtrar por búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }
        
        $users = $query->select('id', 'name', 'email', 'rol', 'telefono', 'sector_asignado', 'area_especialidad')
                      ->orderBy('name')
                      ->limit(20)
                      ->get()
                      ->map(function($user) {
                          return [
                              'id' => $user->id,
                              'name' => $user->name,
                              'email' => $user->email,
                              'rol' => $user->rol,
                              'telefono' => $user->telefono,
                              'sector' => $user->sector_asignado,
                              'area' => $user->area_especialidad,
                              'display_name' => $user->name . ' (' . $this->getRoleDisplayName($user->rol) . ')',
                              'role_badge_class' => $this->getRoleBadgeClass($user->rol)
                          ];
                      });
        
        return response()->json($users);
    }

    /**
     * Verificar estado de sesión para AJAX
     */
    public function checkSession()
    {
        if (!Auth::check()) {
            return response()->json([
                'authenticated' => false,
                'message' => 'Sesión expirada'
            ], 401);
        }

        $user = Auth::user();
        
        // Verificar si la cuenta sigue activa
        if (!$user->activo) {
            Auth::logout();
            return response()->json([
                'authenticated' => false,
                'message' => 'Cuenta desactivada'
            ], 403);
        }

        return response()->json([
            'authenticated' => true,
            'user_name' => $user->name,
            'last_activity' => now()->format('d/m/Y H:i:s')
        ]);
    }

    // =====================================================
    // MÉTODOS PRIVADOS ACTUALIZADOS
    // =====================================================

    /**
     * Redirección por rol - Solo rutas existentes
     *
     * Roles finales (9):
     * - administrador, sectorista, encargado_ingenieria, encargado_laboratorio
     * - encargado_logistico, coordinador_operaciones, asistente_contable
     * - gestor_radiodifusion, visor
     */
    private function getRedirectRouteByRole(string $rol): string
    {
        return match($rol) {
            'administrador' => route('dashboard'),
            'sectorista' => route('incidencias.index'),
            'encargado_ingenieria' => route('incidencias.index'),
            'encargado_laboratorio' => route('incidencias.index'),
            'encargado_logistico' => route('dashboard'),
            'coordinador_operaciones' => route('estaciones.index'),
            'asistente_contable' => route('dashboard'),
            'gestor_radiodifusion' => route('tramites.index'),
            'visor' => route('dashboard'),
            default => route('dashboard')
        };
    }

    /**
     * Registrar acceso al sistema para auditoría
     */
    private function registrarAccesoSistema($user, $action, $request, $additional_data = [])
    {
        try {
            DB::table('auditoria_accesos')->insert([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_rol' => $user->rol,
                'action' => $action,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'session_id' => $request->session()->getId(),
                'additional_data' => !empty($additional_data) ? json_encode($additional_data) : null,
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            // Log error but don't break login/logout process
            Log::error("Error registrando acceso: " . $e->getMessage());
        }
    }

    /**
     * Nombres de roles para display
     */
    private function getRoleDisplayName($rol): string
    {
        return match($rol) {
            'administrador' => 'Administrador',
            'sectorista' => 'Sectorista',
            'encargado_ingenieria' => 'Encargado de Ingeniería',
            'encargado_laboratorio' => 'Encargado de Laboratorio',
            'encargado_logistico' => 'Encargado Logístico',
            'coordinador_operaciones' => 'Coordinador de Operaciones',
            'asistente_contable' => 'Asistente Contable',
            'gestor_radiodifusion' => 'Gestor de Radiodifusión',
            'visor' => 'Visor',
            default => ucfirst(str_replace('_', ' ', $rol))
        };
    }

    /**
     * Clases CSS para badges de roles
     */
    private function getRoleBadgeClass($rol): string
    {
        return match($rol) {
            'administrador' => 'bg-danger text-white',
            'sectorista' => 'bg-primary text-white',
            'encargado_ingenieria' => 'bg-info text-white',
            'encargado_laboratorio' => 'bg-warning text-dark',
            'encargado_logistico' => 'bg-success text-white',
            'coordinador_operaciones' => 'bg-dark text-white',
            'asistente_contable' => 'bg-teal text-white',
            'gestor_radiodifusion' => 'bg-orange text-white',
            'visor' => 'bg-secondary text-white',
            default => 'bg-light text-dark'
        };
    }
}