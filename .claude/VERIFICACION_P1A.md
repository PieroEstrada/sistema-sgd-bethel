# 🧪 VERIFICACIÓN RÁPIDA - FASE P1A

## Instrucciones de Prueba

### 1. Verificar Comandos Registrados

```bash
php artisan list | grep bethel
```

**Deberías ver:**
```
bethel:check-estaciones-fa
bethel:check-incidencias-estancadas
bethel:check-licencias
```

---

### 2. Probar Comando de Licencias

```bash
php artisan bethel:check-licencias --force
```

**Verificar:**
- ✅ Se ejecuta sin errores
- ✅ Muestra lista de licencias vencidas
- ✅ Muestra resumen con estadísticas
- ✅ Los días mostrados son positivos (no negativos)

---

### 3. Verificar Notificaciones Generadas

```bash
php artisan tinker
```

```php
// Ver total de notificaciones
\DB::table('notifications')->count();
// Debería ser > 0 después de ejecutar comandos

// Ver última notificación generada
$notif = \DB::table('notifications')->latest()->first();
echo json_encode(json_decode($notif->data), JSON_PRETTY_PRINT);

// Ver notificaciones por tipo
\DB::table('notifications')
    ->select(\DB::raw('JSON_EXTRACT(data, "$.type") as type'), \DB::raw('count(*) as total'))
    ->groupBy('type')
    ->get();

exit
```

---

### 4. Acceder al Centro de Notificaciones

**Iniciar servidor:**
```bash
composer run dev
# O: php artisan serve
```

**Login:**
- URL: `http://localhost:8000/login`
- Email: `admin@bethel.pe`
- Password: `admin123`

**Acceder a notificaciones:**
- URL: `http://localhost:8000/notifications`

**Verificar:**
- ✅ Dashboard con estadísticas (total, no leídas, críticas, etc.)
- ✅ Filtros funcionan (tipo, severidad, sector)
- ✅ Lista de notificaciones con iconos y colores
- ✅ Botón "Ver detalle" redirige correctamente
- ✅ Botón "Marcar como leída" funciona
- ✅ Botón "Eliminar" funciona
- ✅ "Marcar todas como leídas" funciona

---

### 5. Probar Filtros

**Filtro por tipo:**
- Seleccionar "Licencias vencidas" → Click en "Filtrar"
- Debería mostrar solo notificaciones de tipo `licencia_vencida`

**Filtro por severidad:**
- Seleccionar "Crítica" → Click en "Filtrar"
- Debería mostrar solo notificaciones críticas

**Filtro por sector:**
- Seleccionar "NORTE" → Click en "Filtrar"
- Debería mostrar solo notificaciones de ese sector

---

### 6. Verificar Deduplicación

**Ejecutar el mismo comando dos veces:**
```bash
php artisan bethel:check-licencias
# Esperar 1 segundo
php artisan bethel:check-licencias
```

**Resultado esperado:**
- Primera ejecución: genera notificaciones
- Segunda ejecución: "Alertas duplicadas (omitidas)" > 0

**Forzar sin deduplicación:**
```bash
php artisan bethel:check-licencias --force
```
- Debería generar notificaciones aunque ya existan

---

### 7. Verificar Scheduler Configurado

```bash
php artisan schedule:list
```

**Deberías ver:**
```
0 8 * * * bethel:check-licencias ............................ Next Due: Tomorrow at 08:00 AM
0 9 * * * bethel:check-estaciones-fa ........................ Next Due: Tomorrow at 09:00 AM
0 10 * * * bethel:check-incidencias-estancadas .............. Next Due: Tomorrow at 10:00 AM
```

---

### 8. Ejecutar Scheduler Manualmente (Desarrollo)

**Opción A - Comando watch (recomendado para desarrollo):**
```bash
php artisan schedule:work
```
- Deja este comando corriendo
- Ejecutará los comandos programados en tiempo real

**Opción B - Ejecución manual del scheduler:**
```bash
php artisan schedule:run
```
- Ejecuta solo los comandos que están programados para "ahora"

---

### 9. Verificar Config de Alertas

```bash
php artisan tinker
```

```php
// Ver configuración de licencias
config('alerts.licencias');

// Ver horarios de scheduler
config('alerts.scheduler');

// Ver roles notificados
config('alerts.licencias.roles_notificados');

exit
```

---

### 10. Probar Comandos Individuales

**Estaciones Fuera del Aire:**
```bash
php artisan bethel:check-estaciones-fa --force
```

**Incidencias Estancadas:**
```bash
php artisan bethel:check-incidencias-estancadas --force
```

**Verificar que cada comando:**
- ✅ Se ejecuta sin errores
- ✅ Muestra resumen con estadísticas
- ✅ Genera notificaciones en BD

---

## ✅ Checklist de Verificación

- [ ] Comandos listados correctamente con `php artisan list`
- [ ] Comando de licencias ejecuta sin errores
- [ ] Notificaciones se generan en BD
- [ ] Centro de notificaciones accesible en `/notifications`
- [ ] Dashboard de estadísticas muestra datos correctos
- [ ] Filtros funcionan correctamente
- [ ] Botones de acción funcionan (marcar leída, eliminar)
- [ ] Deduplicación funciona (segunda ejecución omite duplicados)
- [ ] Scheduler configurado y listado correctamente
- [ ] `php artisan schedule:work` ejecuta comandos en tiempo real
- [ ] Config de alertas es accesible desde `config('alerts')`
- [ ] Todos los comandos individuales funcionan

---

## 🐛 Si encuentras errores

### Error: "Class 'App\Notifications\...' not found"
**Solución:**
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### Error: "SQLSTATE[42S02]: Base table or view not found: 'notifications'"
**Solución:**
```bash
php artisan notifications:table
php artisan migrate
```

### Error: Scheduler no se ejecuta
**Solución (Linux/Producción):**
```bash
# Agregar a crontab (crontab -e)
* * * * * cd /path/to/bethel-sgd && php artisan schedule:run >> /dev/null 2>&1
```

**Solución (Windows/Desarrollo):**
```bash
php artisan schedule:work
# Dejar corriendo en una terminal
```

### Error: "Config [alerts] does not exist"
**Solución:**
```bash
php artisan config:clear
php artisan config:cache
```

---

## 📞 Siguiente Paso

Si todas las verificaciones pasan ✅, estás listo para:

**FASE 1B (P1B) - TRANSFERENCIAS DE INCIDENCIAS**

Implementar:
- Método de transferencia en modelo
- Endpoint de transferencia
- Modal de transferencia en vista
- Validación y permisos
- Registro en historial

**O continuar con:**

**FASE 1C (P1C) - EXPORTACIÓN PDF/EXCEL**

Implementar:
- Exportación PDF de incidencias
- Exportación Excel de incidencias
- Botones en UI
- Filtros aplicables

Avísame cuando estés listo para continuar.
