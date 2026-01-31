# 🧪 VERIFICACIÓN RÁPIDA - FASE P1C

## Instrucciones de Prueba - Exportación PDF/Excel de Incidencias

---

### 1. Verificar Rutas Registradas

```bash
php artisan route:list | grep "incidencias.*exportar\|columnas-exportacion"
```

**Deberías ver:**
```
GET|HEAD  incidencias/columnas-exportacion  incidencias.columnas-exportacion
GET|HEAD  incidencias/exportar-excel        incidencias.exportar-excel
GET|HEAD  incidencias/exportar-pdf          incidencias.exportar-pdf
```

---

### 2. Limpiar Cache

```bash
php artisan view:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

---

### 3. Iniciar Servidor

**Iniciar servidor:**
```bash
composer run dev
# O: php artisan serve
```

**Login:**
- URL: `http://localhost:8000/login`
- Email: `admin@bethel.pe`
- Password: `admin123`

---

### 4. Acceder a Lista de Incidencias

**URL:** `http://localhost:8000/incidencias`

✅ **Verificar que aparezca:**
- Botón "Exportar" (verde, con icono de Excel)
- Ubicado junto a "Nueva Incidencia" en el header

---

### 5. Verificar Botón de Exportación

**En la vista de incidencias:**

✅ **Verificar:**
- Header tiene dos botones en grupo:
  - "Nueva Incidencia" (rojo)
  - "Exportar" (verde)

**Click en botón "Exportar"**

---

### 6. Probar Modal de Exportación

**Click en "Exportar":**

✅ **Verificar que el modal contenga:**
- Header verde con título "Exportar Incidencias"
- Alert azul informando sobre aplicación de filtros
- Selector de formato con 2 opciones:
  - PDF (rojo outline)
  - Excel (verde outline)
- Lista de checkboxes con columnas (18 columnas):
  - Código
  - Estación
  - Localidad
  - Título
  - Descripción
  - Prioridad
  - Estado
  - Tipo
  - Área Responsable
  - Reportado Por
  - Asignado A
  - Responsable Actual
  - Fecha Reporte
  - Fecha Resolución
  - Días Transcurridos
  - Costo (S/.)
  - Costo (USD)
  - N° Transferencias
- Tres botones de selección rápida:
  - "Seleccionar Todas"
  - "Por Defecto"
  - "Ninguna"
- Alert gris con contador de incidencias
- Botones: "Cancelar" y "Exportar"

---

### 7. Probar Selección de Columnas

**Test 1: Por defecto**
- Al abrir el modal, deberían estar marcadas 10 columnas por defecto
- Columnas marcadas: Código, Estación, Título, Prioridad, Estado, Área Responsable, Reportado Por, Asignado A, Fecha Reporte, Días Transcurridos

**Test 2: Seleccionar Todas**
- Click en "Seleccionar Todas"
- ✅ Todas las 18 checkboxes deben marcarse

**Test 3: Ninguna**
- Click en "Ninguna"
- ✅ Todas las checkboxes deben desmarcarse

**Test 4: Por Defecto (restaurar)**
- Click en "Por Defecto"
- ✅ Debe restaurar las 10 columnas por defecto marcadas

---

### 8. Exportar a PDF (Sin Filtros)

**Configuración:**
1. Formato: PDF (seleccionar radio button)
2. Columnas: Dejar por defecto (o seleccionar manualmente)
3. Click en "Exportar"

✅ **Verificar:**
- Se descarga archivo `incidencias_2026-01-XX.pdf`
- Modal se cierra automáticamente
- Abrir el PDF:
  - Header rojo con título "Sistema SGD Bethel - Listado de Incidencias"
  - Fecha de generación visible
  - 5 cajas de estadísticas con números:
    - Total
    - Abiertas (azul)
    - En Proceso (amarillo)
    - Cerradas (gris)
    - Críticas (rojo)
  - Tabla con las columnas seleccionadas
  - Badges de colores para prioridad y estado
  - Footer con paginación
  - Orientación: Landscape (apaisado)

---

### 9. Exportar a Excel (Sin Filtros)

**Configuración:**
1. Click en "Exportar"
2. Formato: Excel (seleccionar radio button)
3. Columnas: Seleccionar TODAS (click en "Seleccionar Todas")
4. Click en "Exportar"

✅ **Verificar:**
- Se descarga archivo `incidencias_2026-01-XX.xlsx`
- Abrir en Excel/LibreOffice:
  - Primera fila: Cabeceras en negrita, tamaño 12
  - Columnas:
    - Código
    - Estación
    - Localidad
    - Título
    - Descripción
    - Prioridad (CRÍTICA, ALTA, MEDIA, BAJA)
    - Estado (ABIERTA, EN PROCESO, RESUELTA, CERRADA, CANCELADA)
    - Tipo (TÉCNICA, ADMINISTRATIVA, etc.)
    - Área Responsable
    - Reportado Por (nombre)
    - Asignado A (nombre o "Sin asignar")
    - Responsable Actual (nombre o "Sin asignar")
    - Fecha Reporte (dd/mm/yyyy HH:MM)
    - Fecha Resolución ("Pendiente" si no está resuelta)
    - Días Transcurridos ("X días" o "X días (en curso)")
    - Costo (S/.) ("S/. 1,250.00" o "-")
    - Costo (USD) ("$ 350.50" o "-")
    - N° Transferencias (número)

---

### 10. Exportar con Filtros Aplicados

**Paso 1: Aplicar filtros**
- En la vista de incidencias, aplicar filtros:
  - Prioridad: Crítica
  - Estado: Abierta
- Click en "Filtrar"
- Verificar que la tabla muestra solo incidencias críticas abiertas

**Paso 2: Exportar con filtros**
- Click en "Exportar"
- Formato: PDF
- Columnas: Por defecto
- Click en "Exportar"

✅ **Verificar en el PDF:**
- Sección "Filtros Aplicados" (fondo amarillo) con:
  - "Prioridad: Critica"
  - "Estado: Abierta"
- Sección "Resumen Visual de Estados" con mini gráficos
- Tabla solo contiene incidencias críticas abiertas
- Estadísticas reflejan solo las incidencias filtradas

---

### 11. Validar Selección de Columnas

**Test: Exportar sin columnas**
1. Click en "Exportar"
2. Click en "Ninguna" (deseleccionar todas)
3. Click en "Exportar"

✅ **Verificar:**
- Alert JavaScript: "Debes seleccionar al menos una columna para exportar"
- Modal NO se cierra
- NO se descarga archivo

---

### 12. Probar Diferentes Combinaciones de Columnas

**Test 1: Solo información básica**
- Columnas: Código, Estación, Título, Estado
- Formato: PDF
- ✅ PDF debe mostrar solo esas 4 columnas

**Test 2: Información financiera**
- Columnas: Código, Estación, Título, Costo (S/.), Costo (USD)
- Formato: Excel
- ✅ Excel debe mostrar solo esas 5 columnas

**Test 3: Información de asignación**
- Columnas: Código, Área Responsable, Reportado Por, Asignado A, Responsable Actual, Transferencias
- Formato: PDF
- ✅ PDF debe mostrar solo esas 6 columnas

---

### 13. Probar con Usuario Sectorista

**Login como Sectorista:**
- Logout de admin
- Login con sectorista (si existe, o crear uno):
  - Email: `sectorista@bethel.pe`
  - Password: `bethel123`

**Acceder a incidencias:**
- URL: `http://localhost:8000/incidencias`
- ✅ Debe ver solo incidencias de su sector

**Exportar:**
- Click en "Exportar"
- Formato: Excel
- Columnas: Todas
- Click en "Exportar"

✅ **Verificar:**
- Excel contiene SOLO incidencias del sector del sectorista
- NO contiene incidencias de otros sectores

---

### 14. Verificar Badges y Colores en PDF

**Abrir cualquier PDF exportado:**

✅ **Prioridad badges:**
- Crítica → Fondo rojo
- Alta → Fondo amarillo
- Media → Fondo azul claro
- Baja → Fondo verde

✅ **Estado badges:**
- Abierta → Fondo azul
- En Proceso → Fondo amarillo (texto negro)
- Resuelta → Fondo verde
- Cerrada → Fondo gris
- Cancelada → Fondo negro

✅ **Tipo badges (si columna incluida):**
- TÉC (Técnica) → Fondo azul claro
- ADM (Administrativa) → Fondo azul claro
- OPE (Operativa) → Fondo azul claro
- INF (Infraestructura) → Fondo azul claro

✅ **Días transcurridos badges:**
- 0-3 días (resuelta) → Verde
- 4-7 días (resuelta) → Amarillo
- 8+ días (resuelta) → Rojo
- 0-3 días (abierta) → Azul
- 4-7 días (abierta) → Amarillo
- 8+ días (abierta) → Rojo

---

### 15. Verificar Estadísticas en PDF

**Abrir PDF:**

✅ **Cajas de estadísticas (primera fila):**
- Total: Número total de incidencias
- Abiertas: Número + porcentaje + barra azul
- En Proceso: Número + porcentaje + barra amarilla
- Cerradas: Número + porcentaje
- Críticas: Número + barra roja

✅ **Mini gráficos (si hay filtros):**
- Sección "Resumen Visual de Estados"
- 5 mini barras con valores:
  - Abiertas
  - En Proceso
  - Cerradas
  - Críticas
  - Efectividad (% cerradas)

---

### 16. Verificar Formato de Datos en Excel

**Abrir Excel exportado:**

✅ **Fechas:**
- Formato: "dd/mm/yyyy HH:mm"
- Ejemplo: "27/01/2026 15:30"

✅ **Costos:**
- Formato: "S/. 1,250.00" o "$ 350.50"
- Sin costo: "-"

✅ **Días Transcurridos:**
- Resuelta: "5 días"
- En curso: "12 días (en curso)"

✅ **Nombres:**
- Reportado Por: Nombre completo del usuario
- Asignado A: Nombre completo o "Sin asignar"
- Responsable Actual: Nombre completo o "Sin asignar"

✅ **Transferencias:**
- Número entero (0, 1, 2, 3...)

---

### 17. Probar Exportación con Muchos Registros

**Si hay +50 incidencias:**
- Exportar a PDF con todas las columnas
- ✅ Verificar que el PDF tiene múltiples páginas
- ✅ Footer con número de página debe incrementar
- ✅ No hay cortes de texto raros

**Si hay +100 incidencias:**
- Exportar a Excel con todas las columnas
- ✅ Verificar que todas las filas están presentes
- ✅ No hay límite de 100 filas (debe exportar todas)

---

### 18. Verificar Responsividad del Modal

**En pantalla grande (desktop):**
- ✅ Columnas organizadas en 2 columnas (col-md-6)
- ✅ Todos los elementos visibles sin scroll

**En pantalla pequeña (móvil - simular con DevTools F12):**
- Cambiar a vista móvil (iPhone/Android)
- ✅ Columnas en 1 sola columna
- ✅ Botones de selección apilados verticalmente
- ✅ Modal scrolleable si es necesario

---

### 19. Verificar Integración con Sistema de Filtros

**Test: Filtros complejos**
1. Aplicar múltiples filtros:
   - Búsqueda: "equipo"
   - Prioridad: Alta
   - Estado: En Proceso
   - Área: Técnica
2. Click en "Filtrar"
3. Verificar resultados en tabla
4. Click en "Exportar"
5. Exportar a PDF

✅ **Verificar:**
- Sección "Filtros Aplicados" muestra los 4 filtros
- Tabla del PDF contiene SOLO las incidencias que cumplen TODOS los filtros
- Estadísticas reflejan solo las incidencias filtradas

---

### 20. Verificar Limpieza de Filtros

**Test: Limpiar filtros y exportar**
1. Con filtros aplicados, click en "Limpiar"
2. Verificar que se remueven todos los filtros
3. Click en "Exportar"
4. Exportar a PDF

✅ **Verificar:**
- PDF NO tiene sección "Filtros Aplicados"
- Tabla contiene TODAS las incidencias (según permisos de usuario)
- Estadísticas reflejan todas las incidencias

---

## ✅ Checklist de Verificación

### Rutas y Cache
- [ ] Rutas registradas correctamente
- [ ] Cache limpiado sin errores

### UI - Modal
- [ ] Botón "Exportar" visible en header
- [ ] Modal se abre al hacer click
- [ ] Modal tiene header verde
- [ ] Alert informativo visible
- [ ] Selector de formato (PDF/Excel) funciona
- [ ] 18 checkboxes de columnas aparecen
- [ ] Columnas por defecto están marcadas
- [ ] Botones de selección rápida funcionan

### Funcionalidad - Selección
- [ ] "Seleccionar Todas" marca todas las checkboxes
- [ ] "Ninguna" desmarca todas las checkboxes
- [ ] "Por Defecto" restaura selección por defecto
- [ ] Validación: al menos 1 columna requerida

### Exportación - PDF
- [ ] PDF se descarga correctamente
- [ ] Nombre: `incidencias_YYYY-MM-DD.pdf`
- [ ] Orientación: Landscape
- [ ] Header rojo corporativo visible
- [ ] 5 cajas de estadísticas con datos
- [ ] Tabla con columnas seleccionadas
- [ ] Badges de colores correctos
- [ ] Footer con paginación

### Exportación - Excel
- [ ] Excel se descarga correctamente
- [ ] Nombre: `incidencias_YYYY-MM-DD.xlsx`
- [ ] Cabeceras en negrita
- [ ] Todas las columnas seleccionadas presentes
- [ ] Datos formateados correctamente
- [ ] Fechas en formato dd/mm/yyyy HH:mm
- [ ] Costos con formato monetario

### Filtros
- [ ] Filtros se aplican a exportación
- [ ] Sección "Filtros Aplicados" aparece en PDF
- [ ] Mini gráficos aparecen cuando hay filtros
- [ ] Estadísticas reflejan filtros aplicados
- [ ] Limpiar filtros funciona correctamente

### Permisos
- [ ] Admin exporta todas las incidencias
- [ ] Sectorista exporta solo de su sector
- [ ] Visor puede exportar (solo lectura)
- [ ] Filtros por rol se aplican correctamente

### Columnas Personalizadas
- [ ] Exportar con 4 columnas funciona
- [ ] Exportar con 18 columnas funciona
- [ ] Exportar con columnas financieras funciona
- [ ] Exportar con columnas de asignación funciona

### Datos y Formato
- [ ] Badges de prioridad con colores correctos
- [ ] Badges de estado con colores correctos
- [ ] Días transcurridos calculados correctamente
- [ ] Nombres de usuarios se muestran
- [ ] "Sin asignar" aparece cuando corresponde
- [ ] Transferencias muestran número correcto

### Responsividad
- [ ] Modal responsivo en desktop
- [ ] Modal responsivo en móvil
- [ ] Columnas en 2 cols (desktop)
- [ ] Columnas en 1 col (móvil)

---

## 🐛 Si encuentras errores

### Error: "Route not found"
**Solución:**
```bash
php artisan route:clear
php artisan cache:clear
php artisan config:clear
```

### Error: Modal no se abre
**Solución:**
- Verificar que Bootstrap JS esté cargado
- Verificar consola del navegador (F12) por errores JS
- Limpiar cache del navegador (Ctrl+F5)

### Error: "columnas is undefined"
**Solución:**
- Verificar que `/incidencias/columnas-exportacion` responde correctamente
- Check Network tab en DevTools
- Verificar que `renderizarColumnas()` se ejecuta

### Error: PDF vacío
**Solución:**
- Verificar que hay incidencias con los filtros aplicados
- Check: `$incidencias->count() > 0` en el controlador
- Verificar sintaxis HTML en `pdf.blade.php`

### Error: Excel corrupto
**Solución:**
- Verificar instalación de `maatwebsite/excel`
- Verificar permisos de escritura en `storage/`
- Clear cache: `php artisan cache:clear`
- Check: `composer show maatwebsite/excel`

### Error: "Method exportarPdf does not exist"
**Solución:**
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### Error: Badges no tienen color en PDF
**Solución:**
- Verificar que los estilos CSS inline estén en `pdf.blade.php`
- DomPDF solo soporta CSS inline, no clases de Bootstrap
- Verificar que no hay errores de sintaxis HTML

### Error: Filtros no se aplican
**Solución:**
- Verificar que `URLSearchParams` obtiene los parámetros correctamente
- Check: `window.location.search` en consola
- Verificar que los nombres de parámetros coinciden con los del controlador

---

## 📞 Siguiente Paso

Si todas las verificaciones pasan ✅, la **FASE 1 (P1) está COMPLETA**:
- ✅ P1A - Notificaciones + Scheduler
- ✅ P1B - Transferencias de Incidencias
- ✅ P1C - Exportación PDF/Excel de Incidencias

**Próximas fases sugeridas:**
- **P2A** - Mejoras en Dashboard
- **P2B** - Sistema de Tickets Interno
- **P2C** - Gestión de Usuarios Mejorada
- **P2D** - Estaciones con Columnas Configurables
- **P2E** - Chat Interno MVP

Avísame cuando estés listo para continuar con la siguiente fase o si necesitas ajustes en P1C.
