# Sistema SGD Bethel - Gestión Integral de Estaciones de Radio y TV

## 📻 Descripción del Sistema

El **Sistema SGD (Sistema de Gestión de Documentos) Bethel** es una aplicación web integral desarrollada en Laravel para la gestión completa de estaciones de radio y televisión de la Asociación Cultural Bethel en Perú. El sistema incluye gestión de estaciones, incidencias técnicas, trámites MTC, digitalización de documentos y informes económicos.

## 🎯 Características Principales

### 📡 **Gestión de Estaciones**
- **25+ estaciones reales** distribuidas en 3 sectores (Norte, Centro, Sur)
- Información técnica completa (frecuencia, potencia, coordenadas)
- Estados en tiempo real: Al Aire, Fuera del Aire, Mantenimiento, No Instalada
- Mapa interactivo con Leaflet.js mostrando ubicaciones GPS reales
- Sectorización geográfica con estadísticas por región
- Fichas técnicas detalladas por estación

### 🚨 **Sistema de Incidencias**
- **40+ incidencias realistas** basadas en problemas típicos de radio/TV
- Niveles de prioridad: Crítica, Alta, Media, Baja
- Estados: Abierta, En Proceso, Resuelta, Cerrada
- Asignación automática de técnicos
- Sistema de seguimiento con comentarios
- Alertas automáticas para incidencias críticas
- Costos de reparación en soles y dólares

### 📋 **Trámites MTC (Ministerio de Transportes y Comunicaciones)**
- **11 tipos de trámites** extraídos del documento oficial
- Expedientes reales del sistema MTC peruano
- Estados: Presentado, En Proceso, Aprobado, Rechazado, Observado
- Seguimiento de documentos requeridos vs presentados
- Cálculo automático de costos y tiempos
- Alertas por vencimientos

### 🗂️ **Digitalización y Gestión Documental**
- **Estructura de carpetas predefinida** basada en el PDF oficial
- Tipos de documentos: Autorización, Técnico, Financiero, Legal
- Soporte para múltiples formatos: PDF, Word, Excel, AutoCAD, etc.
- Sistema de archivos con metadatos completos
- Búsqueda avanzada por contenido y tipo
- Control de versiones y duplicados

### 👥 **Sistema de Usuarios y Roles**
- **5 roles definidos**: Administrador, Gerente, Jefe de Estación, Operador, Consulta
- Permisos granulares por funcionalidad
- 20+ usuarios de ejemplo con datos realistas
- Sistema de autenticación Laravel Sanctum
- Control de acceso por estación asignada

## 🏗️ **Arquitectura Técnica**

### **Stack Tecnológico**
- **Framework**: Laravel 10
- **Base de Datos**: MySQL
- **Frontend**: Blade Templates + Bootstrap 5
- **JavaScript**: Vanilla JS + Chart.js + Leaflet.js
- **Mapas**: Leaflet con OpenStreetMap
- **Autenticación**: Laravel Breeze/Sanctum

### **Estructura de Datos**
```
📊 Base de Datos:
├── users (Usuarios con roles)
├── estaciones (25+ estaciones reales)
├── incidencias (40+ incidencias técnicas)
├── tramites_mtc (Expedientes MTC reales)
├── carpetas (Estructura jerárquica)
├── archivos (Documentos digitalizados)
└── Tablas de seguimiento y auditoría
```

### **Enums Robustos**
- `RolUsuario`: 5 roles con permisos específicos
- `EstadoEstacion`: Estados operativos (A.A, F.A, N.I, MANT)
- `Banda`: FM, AM, VHF, UHF con validaciones técnicas
- `Sector`: Norte, Centro, Sur con departamentos asignados
- `TipoTramiteMtc`: 11 tipos con documentos y costos reales
- `PrioridadIncidencia`: 4 niveles con tiempos de respuesta

## 📊 **Datos Reales Incluidos**

### **Estaciones de Ejemplo** (Extraídas del PDF)
- **Celendín, Cajamarca** - FM 94.9 (250W, Sector Norte)
- **Chiquian, Ancash** - FM 98.9 (150W, Sector Centro)  
- **Antabamba, Apurímac** - FM 97.9 (500W, Sector Sur)
- **Lima** - FM 102.1 (1000W, Estación principal)
- **Y 15+ estaciones más** con coordenadas GPS reales

### **Trámites MTC Reales**
- `T-401921-2024` - Solicitud Autorización Putina
- `T-365760-2024` - Cambio de Estudio Ccorca
- `T-614279-2022` - Transferencia Challaco (APROBADO)
- `T-362643-2022` - Aumento Potencia Boca Colorado

### **Incidencias Técnicas Típicas**
- Falla en transmisor principal (Crítica, S/3,500)
- Antena desalineada por vientos (Alta, S/1,200)
- Interferencia en frecuencia (Media)
- Sistema UPS defectuoso (Alta, S/2,200)
- Mantenimiento preventivo (Baja, S/300)

## 🚀 **Instalación y Configuración**

### **Requisitos del Sistema**
```bash
- PHP 8.1+
- MySQL 8.0+
- Composer 2.0+
- Node.js 16+ (opcional)
```

### **Instalación Paso a Paso**

1. **Ejecutar script de configuración**:
```bash
chmod +x setup_bethel_system.sh
./setup_bethel_system.sh
```

2. **Configurar base de datos**:
```bash
# Crear base de datos
mysql -u root -p -e "CREATE DATABASE bethel_sgd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Copiar configuración
cp .env.example .env
```

3. **Configurar .env**:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bethel_sgd
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña

APP_NAME="Sistema SGD Bethel"
APP_URL=http://localhost:8000
BETHEL_TIMEZONE="America/Lima"
```

4. **Instalar y configurar**:
```bash
cd bethel-sgd
composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

5. **Iniciar servidor**:
```bash
php artisan serve
```

### **Usuarios de Prueba**

| Usuario | Email | Password | Rol |
|---------|-------|----------|-----|
| Administrador SGD | admin@bethel.pe | admin123 | Administrador |
| Carlos Mendoza | cmendoza@bethel.pe | bethel123 | Gerente |
| Jorge Arturo Sanchez | jsanchez@bethel.pe | bethel123 | Jefe de Estación |
| Luis Fernando Castro | lcastro@bethel.pe | bethel123 | Operador |

## 📱 **Funcionalidades Principales**

### **Dashboard Ejecutivo**
- 📊 **Estadísticas en tiempo real** de todas las estaciones
- 🗺️ **Mapa interactivo** con estados por colores
- 📈 **Gráficos dinámicos** (Chart.js) de incidencias mensuales
- 🚨 **Centro de alertas** automático
- 📋 **Resumen de actividad** reciente

### **Gestión de Estaciones**
- ✅ **CRUD completo** con validaciones técnicas
- 🔍 **Búsqueda avanzada** por múltiples criterios  
- 🗺️ **Vista de mapa** con clustering automático
- 📊 **Sectorización** con estadísticas por región
- 📄 **Fichas técnicas** en PDF exportables
- ⚡ **Actualización de estado** en tiempo real

### **Sistema de Incidencias**
- 📝 **Reporte fácil** con asignación automática
- 🏷️ **Clasificación por prioridad** y tipo técnico
- 👥 **Asignación de técnicos** especializados
- 💬 **Sistema de comentarios** y seguimiento
- 📊 **Métricas de resolución** y costos
- 📧 **Notificaciones automáticas** por email

### **Trámites MTC**
- 📋 **11 tipos de trámites** oficiales peruanos
- ✅ **Lista de documentos** requeridos automática
- 💰 **Cálculo de costos** oficiales MTC
- ⏰ **Alertas de vencimiento** automáticas
- 📄 **Seguimiento de estado** en tiempo real
- 📊 **Reportes de cumplimiento** regulatorio

### **Digitalización**
- 📁 **Estructura de carpetas** predefinida por estación
- 📤 **Subida múltiple** con drag & drop
- 🔍 **Búsqueda de contenido** con metadatos
- 👁️ **Visualización en línea** para PDFs e imágenes
- 🔒 **Control de acceso** granular
- 📊 **Estadísticas de almacenamiento**

## 🔧 **Configuración Avanzada**

### **Personalización por Cliente**
```php
// config/bethel.php
return [
    'sectores_disponibles' => ['NORTE', 'CENTRO', 'SUR'],
    'tipos_documentos' => ['tecnico', 'legal', 'financiero'],
    'limites_archivos' => [
        'tamaño_maximo' => '50MB',
        'tipos_permitidos' => ['pdf', 'docx', 'xlsx', 'dwg']
    ]
];
```

### **Roles y Permisos**
```php
// Configuración de permisos por rol
'administrador' => ['*'],  // Acceso total
'gerente' => ['ver_dashboard', 'gestionar_estaciones', 'ver_informes'],
'jefe_estacion' => ['ver_estaciones_asignadas', 'gestionar_incidencias'],
'operador' => ['reportar_incidencias', 'subir_archivos'],
'consulta' => ['ver_dashboard', 'ver_informes']
```

## 📈 **Métricas y Estadísticas**

El sistema incluye **dashboards ejecutivos** con:

- 📊 **25+ estaciones** distribuidas geográficamente
- 🚨 **40+ incidencias** con resolución promedio 72h
- 📋 **15+ trámites MTC** en seguimiento activo
- 📁 **100+ archivos** digitalizados por estación
- 👥 **20+ usuarios** con roles específicos
- 🗂️ **Estructura de 50+ carpetas** predefinidas

## 🛡️ **Seguridad y Cumplimiento**

- ✅ **Autenticación robusta** Laravel Sanctum
- 🔐 **Roles y permisos** granulares por funcionalidad
- 🔍 **Auditoría completa** de acciones del usuario
- 🏛️ **Cumplimiento MTC** normativa peruana
- 📊 **Logs detallados** de todas las operaciones
- 🔒 **Protección CSRF** y validación de entrada

## 📞 **Soporte y Mantenimiento**

### **Documentación Técnica**
- 📚 **Manual de usuario** completo incluido
- 🔧 **Guía de administración** del sistema  
- 📊 **Documentación de API** para integraciones
- 🚀 **Scripts de deployment** automatizados

### **Características Técnicas Avanzadas**
- 🔄 **Migraciones automáticas** de base de datos
- 📦 **Seeders con datos reales** del sistema peruano
- 🧪 **Suite de testing** PHPUnit completa
- 🚀 **Optimización de queries** para gran volumen
- 📱 **Responsive design** compatible móviles

## 🎯 **Roadmap y Extensiones**

### **Próximas Características**
- 📱 **App móvil** React Native para técnicos
- 🔔 **Notificaciones push** en tiempo real
- 📊 **Business Intelligence** con Power BI
- 🤖 **Integración con APIs** MTC oficiales
- 🌐 **Multi-idioma** (Español/Inglés)
- ☁️ **Deploy en AWS/Azure** con Docker

---

## 🏆 **Sobre el Sistema**

Este **Sistema SGD Bethel** representa una solución completa y realista para la gestión integral de estaciones de radio y televisión en Perú. Desarrollado con **datos reales extraídos de documentación oficial**, incluye casos de uso auténticos, trámites MTC vigentes, y estructura organizacional real del sector de telecomunicaciones peruano.

El sistema está **listo para producción** con más de **25 estaciones**, **40 incidencias**, **15 trámites**, y **20 usuarios** de ejemplo, proporcionando una experiencia completa desde el primer arranque.

**🚀 ¡Comienza a usar el sistema más completo para gestión de estaciones de radiodifusión en Perú!**