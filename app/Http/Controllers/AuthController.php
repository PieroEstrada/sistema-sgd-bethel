<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            // Actualizar último acceso
            Auth::user()->update(['ultimo_acceso' => now()]);
            
            return redirect()->intended(route('dashboard'))
                           ->with('success', '¡Bienvenido al Sistema SGD Bethel!');
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        $userName = Auth::user()->name ?? 'Usuario';
        
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')
                        ->with('success', "¡Hasta luego, {$userName}!");
    }

    /**
     * Obtener datos del usuario actual para AJAX
     */
    public function currentUser()
    {
        if (!Auth::check()) {
            return response()->json(['authenticated' => false]);
        }

        $user = Auth::user();
        $rol = RolUsuario::from($user->rol);
        
        return response()->json([
            'authenticated' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'rol' => $user->rol,
                'rol_label' => $rol->getLabel(),
                'sector_asignado' => $user->sector_asignado,
                'estaciones_asignadas' => $user->estaciones_asignadas ? json_decode($user->estaciones_asignadas, true) : [],
                'telefono' => $user->telefono,
                'ultimo_acceso' => $user->ultimo_acceso?->format('d/m/Y H:i'),
                
                // 🔐 PERMISOS ESPECÍFICOS
                'permisos' => $rol->getPermisos(),
                'can_assign_incidents' => in_array($user->rol, ['administrador', 'gerente']),
                'can_manage_users' => in_array($user->rol, ['administrador']),
                'can_close_incidents' => in_array($user->rol, ['administrador', 'gerente', 'sectorista', 'jefe_estacion']),
                'can_modify_all_stations' => in_array($user->rol, ['administrador', 'gerente']),
                'can_modify_sector_stations' => $user->rol === 'sectorista',
                'can_only_view_stations' => in_array($user->rol, ['operador', 'consulta']),
                
                // 🎯 NIVEL DE ACCESO
                'nivel_acceso' => $rol->getNivelAcceso(),
                'es_administrativo' => $rol->esAdministrativo(),
                'es_tecnico' => $rol->esTecnico(),
            ]
        ]);
    }

    /**
     * Obtener lista de usuarios para dropdowns
     */
    public function getUsersForDropdown(Request $request)
    {
        $query = User::where('activo', true);
        
        // Filtrar por rol si se especifica
        if ($request->filled('rol')) {
            $query->where('rol', $request->rol);
        }
        
        // Filtrar por búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }
        
        $users = $query->select('id', 'name', 'email', 'rol', 'telefono')
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
                              'display_name' => $user->name . ' (' . ucfirst(str_replace('_', ' ', $user->rol)) . ')',
                              'rol_badge_class' => $this->getRoleBadgeClass($user->rol)
                          ];
                      });
        
        return response()->json($users);
    }

    /**
     * Obtener clase CSS para badge de rol
     */
    private function getRoleBadgeClass($rol)
    {
        return match($rol) {
            'administrador' => 'bg-danger',
            'gerente' => 'bg-warning',
            'jefe_estacion' => 'bg-info',
            'operador' => 'bg-success',
            'consulta' => 'bg-secondary',
            default => 'bg-light text-dark'
        };
    }
}