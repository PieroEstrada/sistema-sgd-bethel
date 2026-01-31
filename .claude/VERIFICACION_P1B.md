# 🧪 VERIFICACIÓN RÁPIDA - FASE P1B

## Instrucciones de Prueba

### 1. Verificar Método en Modelo

```bash
php artisan tinker
```

```php
// Verificar que el método existe
$inc = \App\Models\Incidencia::first();
method_exists($inc, 'transferirResponsabilidad');
// Debe retornar: true

// Verificar que es transferible
$inc->esTransferible();
// Si está abierta o en_proceso: true
// Si está cerrada o cancelada: false

exit
```

---

### 2. Verificar Ruta Registrada

```bash
php artisan route:list | grep transferir
```

**Deberías ver:**
```
POST incidencias/{incidencia}/transferir ......... incidencias.transferir
```

---

### 3. Limpiar Cache

```bash
php artisan view:clear
php artisan config:clear
php artisan cache:clear
```

---

### 4. Acceder a una Incidencia

**Iniciar servidor:**
```bash
composer run dev
# O: php artisan serve
```

**Login:**
- URL: `http://localhost:8000/login`
- Email: `admin@bethel.pe`
- Password: `admin123`

**Abrir incidencia:**
- URL: `http://localhost:8000/incidencias`
- Click en cualquier incidencia (preferentemente abierta o en proceso)

---

### 5. Verificar UI

**En la vista de detalle de incidencia:**

✅ **Verificar que aparezca:**
- Botón "Transferir" (amarillo, con icono de intercambio)
- Ubicación: Entre "Cambiar Estado" y "Volver a Lista"

**Sección "Información de la Incidencia":**
- ✅ Campo "Área Responsable" (si existe)
- ✅ Campo "Responsable Actual" (si existe)
- ✅ Contador de transferencias (si > 0)

---

### 6. Probar Modal de Transferencia

**Click en botón "Transferir":**

✅ **Verificar que el modal contenga:**
- Header amarillo con título "Transferir Responsabilidad"
- Alert azul con información actual:
  - Área actual
  - Responsable actual
  - Contador de transferencias (si aplica)
- Formulario con 3 campos:
  - Select "Área Destino" (requerido)
  - Select "Nuevo Responsable" (opcional)
  - Textarea "Observaciones" (requerido)
- Alert amarillo de advertencia
- Botones: "Cancelar" y "Transferir Responsabilidad"

---

### 7. Ejecutar una Transferencia

**Llenar el formulario:**
1. **Área Destino:** Seleccionar "Logística"
2. **Nuevo Responsable:** Dejar vacío o seleccionar uno
3. **Observaciones:** Escribir: "Transferencia de prueba para verificar funcionalidad P1B"

**Click en "Transferir Responsabilidad"**

✅ **Verificar:**
- Redirección a la misma incidencia
- Mensaje de éxito: "Incidencia transferida exitosamente a Logística"
- Campo "Área Responsable" actualizado a "Logística"
- Si asignaste responsable: campo "Responsable Actual" muestra el nombre
- Contador de transferencias incrementado

---

### 8. Verificar Historial

**En la misma vista de la incidencia:**

Scroll hacia abajo hasta la sección "Historial de Cambios"

✅ **Verificar que aparezca:**
- Nuevo evento con icono amarillo (fa-share)
- Tipo: "Transferencia de Área"
- Descripción: "Transferida de '{área anterior}' a 'Logística'"
- Observaciones: "Transferencia de prueba para verificar funcionalidad P1B"
- De: {área anterior} → A: Logística
- Si asignaste responsable: Responsable: {nombre}
- Usuario que ejecutó: tu nombre
- Fecha y hora del evento

---

### 9. Verificar Notificación (si asignaste responsable)

**Login con el usuario asignado:**

Logout de admin → Login con el usuario asignado

**Verificar notificaciones:**
- Click en campana de notificaciones (navbar)
- Debe aparecer nueva notificación:
  - Título: "Incidencia transferida a tu área"
  - Icono: exchange-alt (intercambio)
  - Color: primary (azul)
  - Link: Al hacer click, redirige a la incidencia

**O acceder al centro de notificaciones:**
- URL: `http://localhost:8000/notifications`
- Filtrar por tipo: "Transferencias"
- Debe aparecer la notificación

---

### 10. Verificar Base de Datos

```bash
php artisan tinker
```

```php
// Obtener la incidencia que transferiste
$inc = \App\Models\Incidencia::find(1); // Cambiar ID según tu incidencia

// Verificar campos actualizados
echo "Área: " . $inc->area_responsable_actual . "\n";
echo "Responsable ID: " . $inc->responsable_actual_user_id . "\n";
echo "Contador: " . $inc->contador_transferencias . "\n";
echo "Última transferencia: " . $inc->fecha_ultima_transferencia . "\n";

// Verificar registro en historial
$ultimaTransferencia = $inc->historial()
    ->where('tipo_accion', 'transferencia_area')
    ->latest()
    ->first();

if ($ultimaTransferencia) {
    echo "\n✅ Transferencia registrada en historial:\n";
    echo "  Área anterior: " . $ultimaTransferencia->area_anterior . "\n";
    echo "  Área nueva: " . $ultimaTransferencia->area_nueva . "\n";
    echo "  Observaciones: " . $ultimaTransferencia->observaciones . "\n";
    echo "  Usuario: " . $ultimaTransferencia->usuarioAccion->name . "\n";
} else {
    echo "\n❌ ERROR: No se registró en historial\n";
}

// Verificar notificación generada (si asignaste responsable)
if ($inc->responsable_actual_user_id) {
    $notif = \DB::table('notifications')
        ->where('data->type', 'incidencia_transferida')
        ->where('data->incidencia_id', $inc->id)
        ->latest()
        ->first();

    if ($notif) {
        echo "\n✅ Notificación generada correctamente\n";
        $data = json_decode($notif->data);
        echo "  Título: " . $data->titulo . "\n";
        echo "  Para usuario: " . $inc->responsableActual->name . "\n";
    } else {
        echo "\n⚠️ No se encontró notificación\n";
    }
}

exit
```

---

### 11. Probar Restricciones de Permisos

**Test 1: Usuario sin permisos**

Login con usuario "Visor":
- Email: `lcastro@bethel.pe`
- Password: `bethel123`

Abrir cualquier incidencia:
- ❌ Botón "Transferir" NO debe aparecer

**Test 2: Incidencia cerrada**

Como admin, abrir una incidencia cerrada:
- ❌ Botón "Transferir" NO debe aparecer
- Si intentas POST directo: Error "Esta incidencia no puede ser transferida..."

**Test 3: Sectorista fuera de su sector**

Login como sectorista (si existe):

Abrir incidencia de OTRO sector:
- ❌ Botón "Transferir" NO debe aparecer

Abrir incidencia de SU sector:
- ✅ Botón "Transferir" SÍ debe aparecer

---

### 12. Probar Validaciones

**Click en "Transferir" y enviar formulario vacío:**

✅ **Verificar mensajes de error:**
- "Debe especificar el área destino"
- "Las observaciones son obligatorias..."

**Escribir solo 5 caracteres en observaciones:**
- ❌ Error: "Las observaciones deben tener al menos 10 caracteres"

**Escribir 600 caracteres en observaciones:**
- ❌ Error: "Las observaciones no pueden exceder 500 caracteres"

---

### 13. Probar Múltiples Transferencias

**Transferir la misma incidencia 3 veces:**

1. **Primera transferencia:** A "Logística"
2. **Segunda transferencia:** A "Operaciones"
3. **Tercera transferencia:** A "Técnica"

✅ **Verificar:**
- Contador muestra "3 transferencias"
- Historial muestra 3 eventos de transferencia
- Última fecha actualizada correctamente
- 3 notificaciones generadas (si asignaste responsables)

---

## ✅ Checklist de Verificación

### Código
- [ ] Método `transferirResponsabilidad()` existe en modelo
- [ ] Método `esTransferible()` funciona correctamente
- [ ] Ruta `incidencias.transferir` registrada
- [ ] Cache limpiado sin errores

### UI
- [ ] Botón "Transferir" aparece con permisos correctos
- [ ] Modal se abre al hacer click
- [ ] Modal muestra información actual correctamente
- [ ] Formulario tiene los 3 campos esperados
- [ ] Advertencia visible en modal

### Funcionalidad
- [ ] Transferencia se ejecuta sin errores
- [ ] Mensaje de éxito aparece
- [ ] Campos actualizados en vista
- [ ] Contador incrementa correctamente
- [ ] Fecha actualizada

### Historial
- [ ] Evento aparece en timeline
- [ ] Tipo correcto: "Transferencia de Área"
- [ ] Observaciones se muestran
- [ ] Usuario y fecha visibles

### Notificaciones
- [ ] Notificación enviada al responsable
- [ ] Aparece en campana de navbar
- [ ] Visible en centro de notificaciones
- [ ] Link funciona correctamente

### Base de Datos
- [ ] `area_responsable_actual` actualizado
- [ ] `responsable_actual_user_id` actualizado (si aplica)
- [ ] `contador_transferencias` incrementado
- [ ] `fecha_ultima_transferencia` actualizada
- [ ] Registro en `incidencia_historial` creado
- [ ] Notificación en tabla `notifications` (si aplica)

### Permisos
- [ ] Admin puede transferir todas
- [ ] Coordinador puede transferir todas
- [ ] Sectorista solo de su sector
- [ ] Responsable/asignado puede transferir
- [ ] Visor NO puede transferir
- [ ] Incidencias cerradas NO se pueden transferir

### Validaciones
- [ ] Área requerida funciona
- [ ] Observaciones mínimo 10 chars
- [ ] Observaciones máximo 500 chars
- [ ] Responsable opcional funciona

---

## 🐛 Si encuentras errores

### Error: "Method transferirResponsabilidad does not exist"
**Solución:**
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### Error: "Route [incidencias.transferir] not defined"
**Solución:**
```bash
php artisan route:clear
php artisan route:cache
```

### Error: Modal no se abre
**Solución:**
- Verificar que Bootstrap JS esté cargado
- Verificar consola del navegador (F12) por errores JS
- Limpiar cache del navegador (Ctrl+F5)

### Error: "Variable $usuariosTransferencia is undefined"
**Solución:**
Verificar que el método `show()` pase la variable:
```php
return view('incidencias.show', compact('incidencia', 'permisos', 'usuariosAsignacion', 'usuariosTransferencia', 'estadisticas'));
```

---

## 📞 Siguiente Paso

Si todas las verificaciones pasan ✅, estás listo para:

**FASE 1C (P1C) - EXPORTACIÓN PDF/EXCEL**

Implementar:
- Exportación PDF de incidencias
- Exportación Excel de incidencias
- Botones en UI
- Aplicar filtros
- Columnas seleccionables

Avísame cuando estés listo para continuar.
