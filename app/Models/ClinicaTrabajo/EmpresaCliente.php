<?php

namespace App\Models\ClinicaTrabajo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmpresaCliente extends Model
{
    use HasFactory;

    protected $table = 'empresas_cliente';

    protected $fillable = [
        'razon_social',
        'rfc',
        'giro_actividad',
        'direccion',
        'contacto_nombre',
        'contacto_telefono',
        'contacto_email',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    
    protected static function booted(): void
    {
        static::creating(function (EmpresaCliente $empresa) {
            if (! empty($empresa->folio)) {
                return;
            }

            $prefijo = 'EMP-' . now()->year . '-';

            $ultimo = static::where('folio', 'like', $prefijo . '%')
                ->selectRaw('MAX(CAST(SUBSTRING(folio, ' . (strlen($prefijo) + 1) . ') AS UNSIGNED)) AS ultimo')
                ->value('ultimo') ?? 0;

            $empresa->folio = $prefijo . str_pad($ultimo + 1, 4, '0', STR_PAD_LEFT);
        });
    }

    public function puestosTrabajo(): HasMany
    {
        return $this->hasMany(PuestoTrabajo::class, 'empresa_cliente_id');
    }

    public function datosLaborales(): HasMany
    {
        return $this->hasMany(DatosLaboral::class, 'empresa_cliente_id');
    }

    public function accidentesTrabajo(): HasMany
    {
        return $this->hasMany(AccidenteTrabajo::class, 'empresa_cliente_id');
    }

    public function valoracionesOcupacionales(): HasMany
    {
        return $this->hasMany(ValoracionOcupacional::class, 'empresa_cliente_id');
    }
}