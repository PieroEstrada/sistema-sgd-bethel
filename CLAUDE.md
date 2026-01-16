# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Sistema SGD Bethel - A Laravel application for managing radio/TV stations for Asociación Cultural Bethel in Peru. Handles station management, technical incidents, MTC (regulatory) procedures, and document digitalization.

## Common Commands

```bash
# Full setup (install deps, generate key, migrate, build frontend)
composer run setup

# Development server (runs Artisan, Queue, Pail logs, and Vite concurrently)
composer run dev

# Run tests
composer run test

# Individual commands
php artisan serve              # Start dev server only
php artisan migrate --seed     # Run migrations with seeders
php artisan tinker             # Interactive shell
npm run dev                    # Vite dev server only
npm run build                  # Production build
```

## Tech Stack

- **Backend**: Laravel 12 (PHP 8.2+)
- **Database**: MySQL 8.0+
- **Frontend**: Blade Templates + Tailwind CSS + Alpine.js
- **Build**: Vite 7
- **Auth**: Laravel Sanctum/Breeze
- **Maps**: Leaflet.js with OpenStreetMap
- **Exports**: DomPDF, Maatwebsite Excel

## Architecture

### Enum-Driven Domain Model

The application heavily uses PHP 8.1+ enums in `app/Enums/` for type safety:

- `RolUsuario` - 10 user roles with granular permissions
- `Sector` - Geographic sectors (Norte, Centro, Sur) with department mappings
- `EstadoEstacion` - Station states (Al Aire, Fuera del Aire, Mantenimiento, No Instalada)
- `EstadoIncidencia` - Incident lifecycle states
- `PrioridadIncidencia` - Priority levels with response time expectations
- `TipoTramiteMtc` - 11 MTC procedure types with required documents and costs
- `Banda` - Radio/TV bands (FM, AM, VHF, UHF)

### Key Models and Relationships

- **User** (`app/Models/User.php`) - Extended with role-based permissions, sector assignments, and station assignments. Contains permission checking methods.
- **Estacion** - Radio/TV stations with GPS coordinates, frequency, power, sector
- **Incidencia** - Technical incidents with priority, cost tracking (soles/dollars), resolution tracking
- **TramiteMtc** - MTC regulatory procedures with document tracking
- **Presbitero** - Presbyterian ministers with sector-based code generation
- **Archivo/Carpeta** - Hierarchical document storage system

### Access Control Pattern

The system uses sector-based and station-based access control:
- Users can be restricted to specific geographic sectors (Norte/Centro/Sur)
- "Jefe de Estación" role is assigned to specific stations
- Permission methods in User model check role + sector + station assignments

### Controller Organization

- Standard CRUD controllers for each entity
- Separate `*AjaxController` classes for async operations
- Export functionality in controllers using DomPDF and Excel packages

### Route Structure

All routes in `routes/web.php` follow RESTful conventions with resource routing. Protected by auth middleware.

## Database

MySQL with utf8mb4 collation. Key tables:
- `users` - With role, sector, and station assignments
- `estaciones` - Station technical data with GPS coordinates
- `incidencias` - Incidents with soft deletes and audit trail
- `tramites_mtc` - MTC procedure tracking
- `carpetas/archivos` - Document folder hierarchy
- `presbiteros` - Minister management
- `auditoria_incidencias` - Audit logs

Run seeders with `php artisan migrate --seed` for realistic test data (25+ stations, 40+ incidents, 15+ MTC procedures).

## Testing

```bash
composer run test           # Full test suite
php artisan test            # Same as above
php artisan test --filter=TestName  # Single test
```

Tests use PHPUnit 11 with SQLite in-memory database (configured in `phpunit.xml`).

## Test Users

| Email | Password | Role |
|-------|----------|------|
| admin@bethel.pe | admin123 | Administrador |
| cmendoza@bethel.pe | bethel123 | Gerente |
| jsanchez@bethel.pe | bethel123 | Jefe de Estación |
| lcastro@bethel.pe | bethel123 | Operador |
