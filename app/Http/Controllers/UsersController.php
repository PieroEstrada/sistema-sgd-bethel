<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Enums\RolUsuario;
use App\Enums\Sector;

class UsersController extends Controller
{
    /**
     * Listar todos los usuarios
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('rol')) {
            $query->where('rol', $request->rol);
        }

        if ($request->filled('activo')) {
            $query->where('activo', $request->activo === '1');
        }

        if ($request->filled('sector')) {
            $query->where('sector_asignado', $request->sector);
        }

        $usuarios = $query->orderBy('name')->paginate(15)->withQueryString();

        $roles = RolUsuario::cases();
        $sectores = Sector::cases();

        return view('usuarios.index', compact('usuarios', 'roles', 'sectores'));
    }

    /**
     * Formulario para crear nuevo usuario
     */
    public function create()
    {
        $roles = RolUsuario::cases();
        $sectores = Sector::cases();

        return view('usuarios.create', compact('roles', 'sectores'));
    }

    /**
     * Guardar nuevo usuario
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'rol' => 'required|string|in:' . implode(',', array_column(RolUsuario::cases(), 'value')),
            'telefono' => 'nullable|string|max:20',
            'sector_asignado' => 'nullable|string|in:NORTE,CENTRO,SUR',
            'password' => 'nullable|string|min:8',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'rol.required' => 'El rol es obligatorio.',
            'rol.in' => 'El rol seleccionado no es válido.',
            'sector_asignado.in' => 'El sector debe ser NORTE, CENTRO o SUR.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        // Si es sectorista, sector_asignado es obligatorio
        if ($validated['rol'] === 'sectorista' && empty($validated['sector_asignado'])) {
            return back()->withErrors(['sector_asignado' => 'El sector es obligatorio para el rol Sectorista.'])
                        ->withInput();
        }

        // Si no es sectorista, limpiar sector_asignado
        if ($validated['rol'] !== 'sectorista') {
            $validated['sector_asignado'] = null;
        }

        // Password por defecto si no se proporciona
        $password = $validated['password'] ?? 'bethel2024';

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($password),
            'rol' => $validated['rol'],
            'telefono' => $validated['telefono'] ?? null,
            'sector_asignado' => $validated['sector_asignado'] ?? null,
            'activo' => true,
        ]);

        return redirect()->route('usuarios.index')
                        ->with('success', 'Usuario creado correctamente.');
    }

    /**
     * Formulario para editar usuario
     */
    public function edit(User $user)
    {
        $roles = RolUsuario::cases();
        $sectores = Sector::cases();

        return view('usuarios.edit', compact('user', 'roles', 'sectores'));
    }

    /**
     * Actualizar usuario
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rol' => 'required|string|in:' . implode(',', array_column(RolUsuario::cases(), 'value')),
            'telefono' => 'nullable|string|max:20',
            'sector_asignado' => 'nullable|string|in:NORTE,CENTRO,SUR',
            'activo' => 'required|boolean',
            'password' => 'nullable|string|min:8',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'rol.required' => 'El rol es obligatorio.',
            'rol.in' => 'El rol seleccionado no es válido.',
            'sector_asignado.in' => 'El sector debe ser NORTE, CENTRO o SUR.',
            'activo.required' => 'El estado es obligatorio.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        // Si es sectorista, sector_asignado es obligatorio
        if ($validated['rol'] === 'sectorista' && empty($validated['sector_asignado'])) {
            return back()->withErrors(['sector_asignado' => 'El sector es obligatorio para el rol Sectorista.'])
                        ->withInput();
        }

        // Si no es sectorista, limpiar sector_asignado
        if ($validated['rol'] !== 'sectorista') {
            $validated['sector_asignado'] = null;
        }

        $user->name = $validated['name'];
        $user->rol = $validated['rol'];
        $user->telefono = $validated['telefono'] ?? null;
        $user->sector_asignado = $validated['sector_asignado'];
        $user->activo = $validated['activo'];

        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('usuarios.index')
                        ->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Desactivar usuario (soft delete)
     */
    public function destroy(User $user)
    {
        // No permitir que el admin se desactive a sí mismo
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puede desactivar su propia cuenta.');
        }

        $user->activo = false;
        $user->save();

        return redirect()->route('usuarios.index')
                        ->with('success', "Usuario {$user->name} desactivado correctamente.");
    }

    /**
     * Reactivar usuario
     */
    public function reactivar(User $user)
    {
        $user->activo = true;
        $user->save();

        return redirect()->route('usuarios.index')
                        ->with('success', "Usuario {$user->name} reactivado correctamente.");
    }
}
