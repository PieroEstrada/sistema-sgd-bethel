<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Carpeta extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'carpetas';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estacion_id',
        'carpeta_padre_id',
        'tipo',
        'nivel',
        'orden',
        'color',
        'icono',
        'creado_por'
    ];

    protected $casts = [
        'nivel' => 'integer',
        'orden' => 'integer'
    ];

    // Relaciones
    public function estacion()
    {
        return $this->belongsTo(Estacion::class);
    }

    public function carpetaPadre()
    {
        return $this->belongsTo(self::class, 'carpeta_padre_id');
    }

    public function carpetasHijas()
    {
        return $this->hasMany(self::class, 'carpeta_padre_id')->orderBy('orden');
    }

    public function archivos()
    {
        return $this->hasMany(Archivo::class);
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    // Scopes
    public function scopeRaiz($query)
    {
        return $query->whereNull('carpeta_padre_id');
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorNivel($query, int $nivel)
    {
        return $query->where('nivel', $nivel);
    }

    public function scopeOrdenadas($query)
    {
        return $query->orderBy('orden')->orderBy('nombre');
    }

    // Métodos de negocio
    public function getRutaCompletaAttribute(): string
    {
        $ruta = [$this->nombre];
        $carpeta = $this->carpetaPadre;
        
        while ($carpeta) {
            $ruta[] = $carpeta->nombre;
            $carpeta = $carpeta->carpetaPadre;
        }
        
        return implode(' > ', array_reverse($ruta));
    }

    public function getIconoCompletoAttribute(): string
    {
        return $this->icono ?? match($this->tipo) {
            'documentacion' => 'fas fa-folder-open text-primary',
            'tecnica' => 'fas fa-cog text-info',
            'financiera' => 'fas fa-dollar-sign text-success',
            'legal' => 'fas fa-gavel text-warning',
            'incidencias' => 'fas fa-exclamation-triangle text-danger',
            default => 'fas fa-folder text-secondary'
        };
    }

    public function getColorCompletoAttribute(): string
    {
        return $this->color ?? match($this->tipo) {
            'documentacion' => 'primary',
            'tecnica' => 'info',
            'financiera' => 'success',
            'legal' => 'warning',
            'incidencias' => 'danger',
            default => 'secondary'
        };
    }

    public function getTotalArchivosAttribute(): int
    {
        return $this->archivos()->count() + 
               $this->carpetasHijas->sum(fn($carpeta) => $carpeta->total_archivos);
    }

    public function getTamanoTotalAttribute(): int
    {
        return $this->archivos()->sum('tamano') + 
               $this->carpetasHijas->sum(fn($carpeta) => $carpeta->tamano_total);
    }

    public function esRaiz(): bool
    {
        return is_null($this->carpeta_padre_id);
    }

    public function tieneHijas(): bool
    {
        return $this->carpetasHijas()->count() > 0;
    }

    public function tieneArchivos(): bool
    {
        return $this->archivos()->count() > 0;
    }

    public function estaVacia(): bool
    {
        return !$this->tieneHijas() && !$this->tieneArchivos();
    }

    public function puedeSerEliminada(): bool
    {
        return $this->estaVacia();
    }

    public function getDescendientes(): \Illuminate\Support\Collection
    {
        $descendientes = collect();
        
        foreach ($this->carpetasHijas as $hija) {
            $descendientes->push($hija);
            $descendientes = $descendientes->merge($hija->getDescendientes());
        }
        
        return $descendientes;
    }

    public function getAncestros(): \Illuminate\Support\Collection
    {
        $ancestros = collect();
        $carpeta = $this->carpetaPadre;
        
        while ($carpeta) {
            $ancestros->push($carpeta);
            $carpeta = $carpeta->carpetaPadre;
        }
        
        return $ancestros->reverse();
    }

    public function moverA(self $nuevoPadre = null): void
    {
        $this->update([
            'carpeta_padre_id' => $nuevoPadre?->id,
            'nivel' => $nuevoPadre ? $nuevoPadre->nivel + 1 : 1
        ]);
        
        // Actualizar nivel de todas las carpetas hijas
        $this->actualizarNivelesHijas();
    }

    public function actualizarNivelesHijas(): void
    {
        foreach ($this->carpetasHijas as $hija) {
            $hija->update(['nivel' => $this->nivel + 1]);
            $hija->actualizarNivelesHijas();
        }
    }

    // Métodos estáticos para estructura predefinida
    public static function crearEstructuraPredefinida(Estacion $estacion): void
    {
        // Crear estructura basada en el PDF del sistema Bethel
        $estructuras = [
            [
                'nombre' => 'SUB AREAS',
                'tipo' => 'documentacion',
                'hijas' => [
                    [
                        'nombre' => 'DOCUMENTACIÓN',
                        'tipo' => 'documentacion',
                        'hijas' => [
                            ['nombre' => 'AUTORIZACION', 'tipo' => 'legal'],
                            ['nombre' => 'RENOVACION', 'tipo' => 'legal'],
                            ['nombre' => 'TRANSFERENCIA', 'tipo' => 'legal'],
                            ['nombre' => 'MODIFICACION DE UBICACION', 'tipo' => 'legal'],
                            ['nombre' => 'MODIFICACION DE POTENCIA', 'tipo' => 'tecnica'],
                            ['nombre' => 'MODIFICACION DE FINALIDAD', 'tipo' => 'legal'],
                            ['nombre' => 'ACTAS DE INSPECCION', 'tipo' => 'tecnica'],
                            ['nombre' => 'HOMOLOGACIONES', 'tipo' => 'tecnica'],
                            ['nombre' => 'OTROS (OFICIOS Y ESCRITOS)', 'tipo' => 'legal']
                        ]
                    ],
                    [
                        'nombre' => 'TECNICA',
                        'tipo' => 'tecnica',
                        'hijas' => [
                            [
                                'nombre' => 'INGENIERIA',
                                'tipo' => 'tecnica',
                                'hijas' => [
                                    ['nombre' => 'MANUAL TECNICO DE EQUIPOS', 'tipo' => 'tecnica'],
                                    ['nombre' => 'ESTUDIO DE COBERTURA', 'tipo' => 'tecnica'],
                                    ['nombre' => 'ESTUDIO DE COMPATIBILIDAD', 'tipo' => 'tecnica'],
                                    ['nombre' => 'ESTUDIO DE RNI', 'tipo' => 'tecnica'],
                                    ['nombre' => 'INFORME TECNICO', 'tipo' => 'tecnica'],
                                    ['nombre' => 'MEMORIA DESCRIPTIVA', 'tipo' => 'tecnica'],
                                    ['nombre' => 'TDT', 'tipo' => 'tecnica']
                                ]
                            ],
                            [
                                'nombre' => 'OPERACIONES',
                                'tipo' => 'tecnica',
                                'hijas' => [
                                    ['nombre' => 'INFORMES TECNICOS', 'tipo' => 'tecnica'],
                                    ['nombre' => 'INFORMES GENERALES', 'tipo' => 'tecnica']
                                ]
                            ],
                            [
                                'nombre' => 'LOGISTICA',
                                'tipo' => 'tecnica',
                                'hijas' => [
                                    ['nombre' => 'ENVIOS', 'tipo' => 'logistica'],
                                    ['nombre' => 'RECOJO', 'tipo' => 'logistica'],
                                    ['nombre' => 'PRESUPUESTO', 'tipo' => 'financiera'],
                                    ['nombre' => 'INVENTARIO', 'tipo' => 'logistica'],
                                    ['nombre' => 'IMPORTACION', 'tipo' => 'logistica'],
                                    ['nombre' => 'ACTA DE ENTREGA', 'tipo' => 'logistica']
                                ]
                            ],
                            [
                                'nombre' => 'LABORATORIO',
                                'tipo' => 'tecnica',
                                'hijas' => [
                                    ['nombre' => 'PRESUPUESTOS', 'tipo' => 'financiera'],
                                    ['nombre' => 'INFORMES', 'tipo' => 'tecnica']
                                ]
                            ]
                        ]
                    ],
                    [
                        'nombre' => 'FINANZAS',
                        'tipo' => 'financiera',
                        'hijas' => [
                            ['nombre' => 'DEPOSITOS', 'tipo' => 'financiera'],
                            ['nombre' => 'GASTOS', 'tipo' => 'financiera'],
                            ['nombre' => 'SALDO', 'tipo' => 'financiera'],
                            ['nombre' => 'RENOVACION', 'tipo' => 'financiera'],
                            ['nombre' => 'CANON', 'tipo' => 'financiera'],
                            ['nombre' => 'PRESTAMO', 'tipo' => 'financiera'],
                            ['nombre' => 'IMPORTACION', 'tipo' => 'financiera']
                        ]
                    ],
                    [
                        'nombre' => 'INCIDENCIAS',
                        'tipo' => 'incidencias'
                    ]
                ]
            ]
        ];

        self::crearEstructuraRecursiva($estacion, $estructuras);
    }

    private static function crearEstructuraRecursiva(Estacion $estacion, array $estructura, ?self $padre = null, int $nivel = 1): void
    {
        foreach ($estructura as $index => $item) {
            $carpeta = self::create([
                'nombre' => $item['nombre'],
                'tipo' => $item['tipo'],
                'estacion_id' => $estacion->id,
                'carpeta_padre_id' => $padre?->id,
                'nivel' => $nivel,
                'orden' => $index + 1,
                'creado_por' => Auth::id()
            ]);

            if (isset($item['hijas'])) {
                self::crearEstructuraRecursiva($estacion, $item['hijas'], $carpeta, $nivel + 1);
            }
        }
    }

    public static function getTiposCarpeta(): array
    {
        return [
            'documentacion' => 'Documentación',
            'tecnica' => 'Técnica',
            'financiera' => 'Financiera',
            'legal' => 'Legal',
            'logistica' => 'Logística',
            'incidencias' => 'Incidencias',
            'otros' => 'Otros'
        ];
    }
}