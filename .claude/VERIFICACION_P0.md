# 🧪 VERIFICACIÓN RÁPIDA - FASE P0

## Instrucciones de Prueba

### 1. Verificar que el servidor esté corriendo

```bash
composer run dev
# O alternativamente:
php artisan serve
```

Accede a: `http://localhost:8000` (o el puerto configurado)

---

### 2. Probar Timeline de Incidencias

#### Opción A: Navegación Manual
1. Login con cualquier usuario de prueba:
   - Email: `admin@bethel.pe`
   - Password: `admin123`

2. Ir a **Incidencias** → **Ver todas**

3. Click en cualquier incidencia para ver el detalle

4. **Verificar:**
   - ✅ No hay error "Undefined array key"
   - ✅ Sección "Historial de Cambios" se muestra correctamente
   - ✅ Aparece al menos 1 evento ("Creación")
   - ✅ El evento muestra: icono, descripción, usuario, fecha

#### Opción B: Acceso Directo
Accede a: `http://localhost:8000/incidencias/1`

---

### 3. Probar Creación de Incidencia

1. Ir a **Incidencias** → **Nueva Incidencia**

2. Llenar el formulario:
   - Título: "Prueba Timeline P0"
   - Descripción: "Verificando que el historial se registre automáticamente"
   - Estación: Seleccionar cualquiera
   - Prioridad: Media

3. Guardar

4. **Verificar:**
   - ✅ Se redirige a la vista de detalle
   - ✅ Sección "Historial de Cambios" aparece
   - ✅ Hay 1 evento de "Creación" con tu nombre de usuario
   - ✅ La fecha del evento coincide con la fecha actual

---

### 4. Verificar en Base de Datos (Opcional)

```bash
php artisan tinker
```

```php
// Obtener una incidencia
$inc = \App\Models\Incidencia::first();

// Ver su historial
$inc->historial;

// Debería mostrar algo como:
// Collection {
//   #items: array:1 [
//     0 => IncidenciaHistorial {
//       #attributes: array:12 [
//         "id" => 1
//         "tipo_accion" => "creacion"
//         "descripcion_cambio" => "Incidencia creada..."
//         ...
//       ]
//     }
//   ]
// }

// Contar total de registros
\App\Models\IncidenciaHistorial::count();
// Debería ser >= 34

exit
```

---

### 5. Verificar Modelo (Opcional)

```bash
php artisan tinker
```

```php
// Verificar que los campos están en fillable
$inc = new \App\Models\Incidencia;
print_r($inc->getFillable());

// Deberías ver:
// - area_responsable_actual
// - responsable_actual_user_id
// - contador_transferencias
// - fecha_ultima_transferencia

// Verificar método esTransferible()
$inc = \App\Models\Incidencia::where('estado', 'abierta')->first();
$inc->esTransferible(); // Debería retornar true

exit
```

---

## ✅ Checklist de Verificación

- [ ] Servidor Laravel corriendo sin errores
- [ ] Login exitoso
- [ ] Vista de listado de incidencias carga correctamente
- [ ] Vista de detalle de incidencia carga SIN error "Undefined array key"
- [ ] Historial de cambios se muestra correctamente
- [ ] Crear nueva incidencia registra automáticamente en historial
- [ ] Modelo tiene campos de transferencia en $fillable
- [ ] Método `esTransferible()` existe y funciona

---

## 🐛 Si encuentras errores

### Error: "Class IncidenciaHistorial not found"
**Solución:**
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### Error: "Undefined array key 'cambios'"
**Solución:** Verifica que hayas actualizado correctamente `resources/views/incidencias/show.blade.php`

### Error: "Column 'area_responsable_actual' not found"
**Solución:**
```bash
php artisan migrate
```

### Error: Timeline no muestra eventos
**Solución:**
```bash
php artisan incidencias:migrar-historial --force
```

---

## 📞 Siguiente Paso

Si todas las verificaciones pasan ✅, estás listo para:

**FASE 1 (P1) - FUNCIONALIDADES CORE**

Avísame cuando estés listo para continuar con:
- P1A: Notificaciones automáticas + Scheduler
- P1B: Transferencias completas
- P1C: Exportación PDF/Excel
