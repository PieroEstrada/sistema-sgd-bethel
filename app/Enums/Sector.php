<?php

namespace App\Enums;

enum Sector: string
{
    case NORTE = 'NORTE';
    case CENTRO = 'CENTRO';
    case SUR = 'SUR';
    // case ORIENTE = 'ORIENTE';

    public function getLabel(): string
    {
        return match($this) {
            self::NORTE => 'Norte',
            self::CENTRO => 'Centro',
            self::SUR => 'Sur',
            // self::ORIENTE => 'Oriente',
        };
    }

    public function label(): string
    {
        return $this->getLabel();
    }

    public function getDescription(): string
    {
        return match($this) {
            self::NORTE => 'Departamentos del norte del Perú',
            self::CENTRO => 'Departamentos del centro del Perú',
            self::SUR => 'Departamentos del sur del Perú',
            // self::ORIENTE => 'Departamentos de la selva peruana',
        };
    }

    public function getDepartamentos(): array
    {
        return match($this) {
            self::NORTE => [
                'Tumbes', 'Piura', 'Lambayeque', 'La Libertad',
                'Cajamarca', 'Amazonas', 'San Martín'
            ],
            self::CENTRO => [
                'Lima', 'Callao', 'Ancash', 'Huánuco', 'Pasco',
                'Junín', 'Huancavelica', 'Ica', 'Loreto', 'Ucayali', 'Madre de Dios'
            ],
            self::SUR => [
                'Arequipa', 'Moquegua', 'Tacna', 'Cusco',
                'Apurímac', 'Ayacucho', 'Puno'
            ],
            // self::ORIENTE => [
            //     'Loreto', 'Ucayali', 'Madre de Dios'
            // ]
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::NORTE => 'success',
            self::CENTRO => 'warning',
            self::SUR => 'danger',
            // self::ORIENTE => 'success',
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::NORTE => 'fas fa-arrow-up',
            self::CENTRO => 'fas fa-dot-circle',
            self::SUR => 'fas fa-arrow-down',
            // self::ORIENTE => 'fas fa-tree',
        };
    }

    public static function getOptions(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->getLabel(),
            'description' => $case->getDescription(),
            'color' => $case->getColor(),
            'icon' => $case->getIcon(),
            'departamentos' => $case->getDepartamentos()
        ], self::cases());
    }

    public static function getSectorPorDepartamento(string $departamento): ?self
    {
        foreach (self::cases() as $sector) {
            if (in_array($departamento, $sector->getDepartamentos())) {
                return $sector;
            }
        }
        return null;
    }

    public function tieneEstaciones(): bool
    {
        // Método para verificar si el sector tiene estaciones asignadas
        return \App\Models\Estacion::where('sector', $this)->exists();
    }

    public function getEstadisticas(): array
    {
        $estaciones = \App\Models\Estacion::where('sector', $this);
        
        return [
            'total' => $estaciones->count(),
            'al_aire' => $estaciones->where('estado', EstadoEstacion::AL_AIRE)->count(),
            'fuera_aire' => $estaciones->where('estado', EstadoEstacion::FUERA_DEL_AIRE)->count(),
            // 'mantenimiento' ya no existe como estado
            'no_instalada' => $estaciones->where('estado', EstadoEstacion::NO_INSTALADA)->count()
        ];
    }
}