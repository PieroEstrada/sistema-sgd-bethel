<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Mostrar formulario de edición del perfil
     */
    public function edit()
    {
        $user = Auth::user();

        return view('profile.edit', compact('user'));
    }

    /**
     * Actualizar perfil del usuario autenticado
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'name' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
        ];

        // Solo validar password si se proporciona
        if ($request->filled('password')) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
            $rules['current_password'] = ['required', 'string'];
        }

        $validated = $request->validate($rules, [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede exceder 255 caracteres.',
            'telefono.max' => 'El teléfono no puede exceder 20 caracteres.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'current_password.required' => 'Debe ingresar su contraseña actual para cambiarla.',
        ]);

        // Verificar contraseña actual si se quiere cambiar
        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'La contraseña actual no es correcta.']);
            }
        }

        // Actualizar datos
        $user->name = $validated['name'];
        $user->telefono = $validated['telefono'] ?? $user->telefono;

        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('profile.edit')
                        ->with('success', 'Perfil actualizado correctamente.');
    }
}
