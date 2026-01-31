# 📋 PRUEBAS DEL SISTEMA DE CHAT

Sistema de mensajería completo implementado con persistencia, polling y badges de no leídos.

## 🎯 CARACTERÍSTICAS IMPLEMENTADAS

### ✅ Persistencia Real
- ✓ Mensajes se guardan en tabla `messages` (BD)
- ✓ Al recargar, el historial persiste
- ✓ Relaciones correctas: from_user_id, to_user_id

### ✅ Entrega al Destinatario (Polling)
- ✓ Polling cada 5 segundos para mensajes nuevos (cuando hay chat abierto)
- ✓ Polling cada 10 segundos para actualizar lista de conversaciones
- ✓ No usa WebSockets (solo HTTP + fetch)

### ✅ Mensajes No Leídos + Badges
- ✓ Badge con número en cada conversación
- ✓ Badge total en header "Conversaciones"
- ✓ Marca automática como leído al abrir conversación
- ✓ Color azul en conversaciones con no leídos

### ✅ Lista de Conversaciones Actualizada
- ✓ Muestra último mensaje (preview)
- ✓ Hora del último mensaje
- ✓ Se actualiza automáticamente al enviar/recibir
- ✓ Conversación sube al top cuando llegan mensajes nuevos

### ✅ UI/UX Completo
- ✓ Bootstrap 5 responsive
- ✓ Modal simple para iniciar nueva conversación
- ✓ Búsqueda de usuarios en tiempo real
- ✓ Loading states en todos los procesos
- ✓ Scroll automático al final al recibir mensajes
- ✓ Doble check (✓✓) cuando mensaje es leído

---

## 🧪 INSTRUCCIONES DE PRUEBA

### PASO 1: Preparar Entorno

#### Usuarios de Prueba:
```bash
# Verificar que existan al menos 2 usuarios
php artisan test:message
```

Esto mostrará:
```
Usuario 1: Abel Cueto (ID: 1)
Usuario 2: Edison Moya (ID: 2)
✓ El mensaje se guardó correctamente en BD
```

#### Iniciar Servidor:
```bash
# Terminal 1
php artisan serve

# Terminal 2 (opcional, para ver logs en tiempo real)
php artisan pail
```

---

### PASO 2: Prueba con 2 Navegadores (Persistencia + Polling)

#### **Navegador 1 (Normal):**
1. Abrir: `http://127.0.0.1:8000/login`
2. Login con: `admin@bethel.pe` / `admin123`
3. Ir a: `http://127.0.0.1:8000/chat`

#### **Navegador 2 (Incógnito):**
1. Abrir ventana incógnita
2. Ir a: `http://127.0.0.1:8000/login`
3. Login con: `cmendoza@bethel.pe` / `bethel123`
4. Ir a: `http://127.0.0.1:8000/chat`

---

### PASO 3: Prueba de Persistencia

#### En Navegador 1 (admin@bethel.pe):
1. Click en botón **"+"** (Nueva Conversación)
2. Buscar: "Carlos Mendoza"
3. Click en **"Mensaje"**
4. Escribir: "Hola Carlos, prueba de persistencia"
5. Click en **Enviar**

#### ✅ Verificar:
- El mensaje aparece en burbuja azul (derecha)
- El mensaje tiene timestamp
- Input se limpia automáticamente

#### En Navegador 1:
1. Presionar F5 (recargar página)
2. Ir nuevamente a `/chat`
3. Click en la conversación con "Carlos Mendoza"

#### ✅ Verificar:
- El mensaje sigue ahí (persistió en BD)
- La hora se muestra correctamente

---

### PASO 4: Prueba de Polling (Recepción en Tiempo Real)

#### En Navegador 2 (cmendoza@bethel.pe):
1. Esperar **máximo 10 segundos**

#### ✅ Verificar:
- Aparece conversación con "Abel Cueto" en la lista izquierda
- Badge rojo con número "1" (mensaje no leído)
- Preview del mensaje: "Hola Carlos, prueba de persistencia"
- Hora relativa (ej: "5m")
- Fondo azul claro en la conversación (no leído)

#### En Navegador 2:
1. Click en la conversación con "Abel Cueto"

#### ✅ Verificar:
- El mensaje aparece en burbuja blanca (izquierda)
- Badge de "1" desaparece automáticamente
- Fondo azul claro desaparece
- El mensaje tiene timestamp

---

### PASO 5: Prueba de Polling Bidireccional

#### En Navegador 2 (cmendoza@bethel.pe):
1. Escribir: "Hola Abel, recibí tu mensaje"
2. Click en **Enviar**

#### ✅ Verificar en Navegador 2:
- Mensaje aparece en burbuja azul (derecha)
- Scroll automático al final

#### En Navegador 1 (admin@bethel.pe):
1. Esperar **máximo 5 segundos** (sin hacer nada)

#### ✅ Verificar en Navegador 1:
- El mensaje de Carlos aparece automáticamente (polling)
- Burbuja blanca (izquierda)
- Scroll automático al final
- La conversación se actualiza en lista izquierda

---

### PASO 6: Prueba de No Leídos (Badges)

#### En Navegador 1 (admin@bethel.pe):
1. Click en botón "Inicio" o Dashboard (salir del chat)

#### En Navegador 2 (cmendoza@bethel.pe):
1. Enviar 3 mensajes:
   - "Mensaje 1 de prueba"
   - "Mensaje 2 de prueba"
   - "Mensaje 3 de prueba"

#### En Navegador 1:
1. Ir nuevamente a `/chat`

#### ✅ Verificar:
- Badge rojo con "3" en conversación con Carlos
- Badge rojo en header "Conversaciones" con total de no leídos
- Fondo azul claro en la conversación
- Preview muestra el último mensaje: "Mensaje 3 de prueba"

#### En Navegador 1:
1. Click en conversación con Carlos

#### ✅ Verificar:
- Los 3 mensajes se muestran
- Badge de "3" desaparece inmediatamente
- Fondo azul claro desaparece
- Todos los mensajes tienen timestamp

---

### PASO 7: Prueba de Doble Check (✓✓)

#### En Navegador 1 (admin@bethel.pe):
1. Enviar mensaje: "¿Has leído este mensaje?"

#### ✅ Verificar en Navegador 1:
- El mensaje aparece con un solo icono de hora
- SIN doble check (Carlos aún no lo ha leído)

#### En Navegador 2 (cmendoza@bethel.pe):
1. Esperar 5-10 segundos para que llegue el mensaje
2. Click en la conversación con Abel (si no está abierta)

#### En Navegador 1:
1. Esperar **5-10 segundos** (polling)

#### ✅ Verificar en Navegador 1:
- El mensaje ahora tiene **doble check** ✓✓
- Indica que Carlos leyó el mensaje

---

### PASO 8: Prueba de Actualización de Lista

#### En Navegador 2:
1. Abrir conversación con Abel
2. Enviar: "Último mensaje de prueba"

#### En Navegador 1:
1. **Sin abrir** la conversación con Carlos
2. Esperar 10 segundos

#### ✅ Verificar en Navegador 1:
- La conversación con Carlos **sube al top** de la lista
- Preview actualiza a: "Último mensaje de prueba"
- Badge se incrementa
- Hora se actualiza a "Ahora" o "1m"

---

### PASO 9: Prueba de Múltiples Conversaciones

#### En Navegador 1:
1. Iniciar conversación con otro usuario (ej: Edison Moya)
2. Enviar mensaje: "Hola Edison"

#### ✅ Verificar:
- Ahora hay 2 conversaciones en la lista
- Badge total en header suma ambas conversaciones
- Cada conversación muestra su propio preview y hora

---

### PASO 10: Prueba de Búsqueda

#### En Navegador 1:
1. Click en **"+"** (Nueva Conversación)
2. En el input de búsqueda escribir: "carlos"

#### ✅ Verificar:
- La lista se filtra en tiempo real
- Solo aparece "Carlos Mendoza"
- Escribir "edison" → solo aparece "Edison Moya"
- Borrar búsqueda → aparecen todos los usuarios

---

## 🐛 VERIFICACIÓN DE BD (Opcional)

Para verificar que los mensajes SÍ se guardan en BD:

```bash
php artisan tinker
```

Ejecutar:
```php
use App\Models\Message;

// Ver todos los mensajes
Message::with(['fromUser:id,name', 'toUser:id,name'])->latest()->take(5)->get();

// Ver count
Message::count();

// Ver mensajes entre 2 usuarios específicos
Message::betweenUsers(1, 2)->get();
```

---

## 📊 CHECKLIST FINAL

### Persistencia:
- [ ] Los mensajes se guardan en BD
- [ ] Al recargar, el historial persiste
- [ ] Los mensajes tienen from_user_id, to_user_id correctos

### Polling:
- [ ] Mensajes nuevos llegan automáticamente (5 seg)
- [ ] Lista de conversaciones se actualiza (10 seg)
- [ ] NO requiere recargar página

### No Leídos:
- [ ] Badge con número en cada conversación
- [ ] Badge total en header
- [ ] Se marca como leído al abrir conversación
- [ ] Fondo azul en conversaciones no leídas

### Lista de Conversaciones:
- [ ] Muestra preview del último mensaje
- [ ] Muestra hora relativa (Ahora, 5m, Ayer, etc.)
- [ ] Se actualiza al enviar/recibir
- [ ] Conversación sube al top con mensajes nuevos

### UI/UX:
- [ ] Responsive en móvil y desktop
- [ ] Loading states visibles
- [ ] Scroll automático al final
- [ ] Doble check en mensajes leídos
- [ ] Búsqueda funciona en modal

---

## 🎬 RESULTADO ESPERADO

Al finalizar todas las pruebas, deberías ver:

✅ **Mensajes persistentes** en BD que no desaparecen al recargar
✅ **Polling funcional** que trae mensajes nuevos sin recargar
✅ **Badges de no leídos** que se actualizan automáticamente
✅ **Lista de conversaciones** con preview y hora que se reordena
✅ **Doble check** en mensajes leídos por el destinatario
✅ **Sistema completo** sin necesidad de WebSockets

---

## 🔧 TROUBLESHOOTING

### Si los mensajes NO se guardan:
1. Verificar que la tabla `messages` existe: `php artisan migrate:status`
2. Probar crear mensaje manual: `php artisan test:message`
3. Ver logs: `php artisan pail` o `tail -f storage/logs/laravel.log`

### Si el polling NO funciona:
1. Abrir DevTools → Network
2. Verificar que cada 5-10 seg aparecen peticiones a `/chat/messages/{id}` y `/chat/conversations`
3. Verificar que no hay errores 401 (auth) o 500 (servidor)

### Si los badges NO aparecen:
1. Abrir consola JS (F12)
2. Verificar que `totalUnread` se calcula correctamente
3. Verificar que `conv.unread_count` tiene valores

---

## 📞 SOPORTE

Si algo no funciona como se describe en este documento:
1. Abrir DevTools → Console
2. Buscar errores en rojo
3. Copiar el mensaje de error completo
4. Revisar `storage/logs/laravel.log`

---

**Última actualización:** 2026-01-28
**Sistema:** Laravel 12 + Bootstrap 5 + Alpine.js
**Sin:** WebSockets, Pusher, Redis
**Con:** HTTP Polling + fetch API
