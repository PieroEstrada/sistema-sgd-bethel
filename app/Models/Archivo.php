<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Archivo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'archivos';

    protected $fillable = [
        'nombre_original',
        'nombre_archivo',
        'ruta',
        'tipo_documento',
        'tamano',
        'extension',
        'mime_type',
        'estacion_id',
        'carpeta_id',
        'tramite_id',
        'incidencia_id',
        'subido_por',
        'descripcion',
        'es_publico',
        'hash_archivo',
        'version'
    ];

    protected $casts = [
        'tamano' => 'integer',
        'es_publico' => 'boolean',
        'version' => 'integer'
    ];

    // Relaciones
    public function estacion()
    {
        return $this->belongsTo(Estacion::class);
    }

    public function carpeta()
    {
        return $this->belongsTo(Carpeta::class);
    }

    public function tramite()
    {
        return $this->belongsTo(TramiteMtc::class, 'tramite_id');
    }

    public function incidencia()
    {
        return $this->belongsTo(Incidencia::class);
    }

    public function subidoPor()
    {
        return $this->belongsTo(User::class, 'subido_por');
    }

    // Scopes
    public function scopePublicos($query)
    {
        return $query->where('es_publico', true);
    }

    public function scopePrivados($query)
    {
        return $query->where('es_publico', false);
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_documento', $tipo);
    }

    public function scopePorExtension($query, string $extension)
    {
        return $query->where('extension', $extension);
    }

    // Métodos de negocio
    public function getTamanoFormateadoAttribute(): string
    {
        $bytes = $this->tamano;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    public function getIconoAttribute(): string
    {
        return match(strtolower($this->extension)) {
            'pdf' => 'fas fa-file-pdf text-danger',
            'doc', 'docx' => 'fas fa-file-word text-primary',
            'xls', 'xlsx' => 'fas fa-file-excel text-success',
            'ppt', 'pptx' => 'fas fa-file-powerpoint text-warning',
            'jpg', 'jpeg', 'png', 'gif', 'bmp' => 'fas fa-file-image text-info',
            'mp3', 'wav', 'ogg' => 'fas fa-file-audio text-secondary',
            'mp4', 'avi', 'mov' => 'fas fa-file-video text-dark',
            'zip', 'rar', '7z' => 'fas fa-file-archive text-warning',
            'txt' => 'fas fa-file-alt text-secondary',
            'dwg', 'dxf' => 'fas fa-drafting-compass text-info',
            'kmz', 'kml' => 'fas fa-map-marked-alt text-success',
            default => 'fas fa-file text-muted'
        };
    }

    public function getColorTipoAttribute(): string
    {
        return match($this->tipo_documento) {
            'autorizacion' => 'success',
            'renovacion' => 'primary',
            'transferencia' => 'warning',
            'tecnico' => 'info',
            'administrativo' => 'secondary',
            'financiero' => 'danger',
            'legal' => 'dark',
            default => 'light'
        };
    }

    public function esImagen(): bool
    {
        return in_array(strtolower($this->extension), [
            'jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'
        ]);
    }

    public function esPdf(): bool
    {
        return strtolower($this->extension) === 'pdf';
    }

    public function esDocumento(): bool
    {
        return in_array(strtolower($this->extension), [
            'doc', 'docx', 'pdf', 'txt', 'rtf'
        ]);
    }

    public function esHoja(): bool
    {
        return in_array(strtolower($this->extension), [
            'xls', 'xlsx', 'csv'
        ]);
    }

    public function esComprimido(): bool
    {
        return in_array(strtolower($this->extension), [
            'zip', 'rar', '7z', 'tar', 'gz'
        ]);
    }

    public function puedeSerVisualizadoEnLinea(): bool
    {
        return $this->esPdf() || $this->esImagen() || 
               in_array(strtolower($this->extension), ['txt', 'html', 'htm']);
    }

    public function getUrlDescargaAttribute(): string
    {
        return route('archivos.descargar', $this);
    }

    public function getUrlVistaAttribute(): string
    {
        if ($this->puedeSerVisualizadoEnLinea()) {
            return route('archivos.ver', $this);
        }
        return $this->getUrlDescargaAttribute();
    }

    public function getRutaCompletaAttribute(): string
    {
        return storage_path('app/' . $this->ruta);
    }

    public function existe(): bool
    {
        return Storage::exists($this->ruta);
    }

    public function obtenerContenido(): string
    {
        return Storage::get($this->ruta);
    }

    public function eliminarArchivo(): bool
    {
        if (Storage::exists($this->ruta)) {
            return Storage::delete($this->ruta);
        }
        return true;
    }

    public function duplicar(User $usuario = null): self
    {
        $nuevoArchivo = $this->replicate();
        $nuevoArchivo->nombre_original = 'Copia de ' . $this->nombre_original;
        $nuevoArchivo->subido_por = $usuario?->id ?? Auth::id();
        $nuevoArchivo->created_at = now();
        $nuevoArchivo->version = $this->version + 1;
        
        // Copiar el archivo físico
        $nuevaRuta = 'archivos/' . uniqid() . '.' . $this->extension;
        Storage::copy($this->ruta, $nuevaRuta);
        $nuevoArchivo->ruta = $nuevaRuta;
        $nuevoArchivo->save();
        
        return $nuevoArchivo;
    }

    public function moverACarpeta(Carpeta $carpeta): void
    {
        $this->update(['carpeta_id' => $carpeta->id]);
    }

    public function actualizarDescripcion(string $descripcion): void
    {
        $this->update(['descripcion' => $descripcion]);
    }

    public function marcarComoPublico(bool $publico = true): void
    {
        $this->update(['es_publico' => $publico]);
    }

    // Métodos estáticos
    public static function getTiposDocumento(): array
    {
        return [
            'autorizacion' => 'Documentos de Autorización',
            'renovacion' => 'Documentos de Renovación',
            'transferencia' => 'Documentos de Transferencia',
            'modificacion' => 'Documentos de Modificación',
            'tecnico' => 'Documentos Técnicos',
            'administrativo' => 'Documentos Administrativos',
            'financiero' => 'Documentos Financieros',
            'legal' => 'Documentos Legales',
            'otros' => 'Otros Documentos'
        ];
    }

    public static function getExtensionesPermitidas(): array
    {
        return [
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
            'jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg',
            'txt', 'csv', 'zip', 'rar',
            'dwg', 'dxf', 'kmz', 'kml',
            'mp3', 'wav', 'mp4', 'avi'
        ];
    }

    public static function getTamanoMaximo(): int
    {
        return 50 * 1024 * 1024; // 50MB
    }

    public static function getEstadisticas(): array
    {
        return [
            'total' => self::count(),
            'por_tipo' => self::select('tipo_documento', DB::raw('count(*) as total'))
                              ->groupBy('tipo_documento')
                              ->pluck('total', 'tipo_documento')
                              ->toArray(),
            'por_extension' => self::select('extension', DB::raw('count(*) as total'))
                                   ->groupBy('extension')
                                   ->orderBy('total', 'desc')
                                   ->limit(10)
                                   ->pluck('total', 'extension')
                                   ->toArray(),
            'tamano_total' => self::sum('tamano'),
            'publicos' => self::where('es_publico', true)->count(),
            'privados' => self::where('es_publico', false)->count()
        ];
    }

    // Eventos del modelo
    protected static function booted()
    {
        static::deleting(function ($archivo) {
            // Eliminar archivo físico cuando se elimina el registro
            $archivo->eliminarArchivo();
        });
    }
}