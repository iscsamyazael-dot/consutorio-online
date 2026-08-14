<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluacionesia extends Model
{
    protected $table = 'evaluaciones_ia';

    protected $fillable = [
        'consulta_id',
        'consulta_folio',
        'session_uuid',
        'sintomas_detectados',
        'diagnostico_probable',
        'indicaciones_medico',
        'recomendacion',
        'confianza',
    ];

    protected $casts = [
        'confianza' => 'decimal:2',
    ];

    protected $appends = ['folio', 'riesgo', 'estado', 'sintomas_array'];

    // Relaciones
    public function consulta()
    {
        return $this->belongsTo(Consulta::class, 'consulta_id');
    }

    public function notaPsoapp()
    {
        return $this->hasOne(NotaPsoapp::class, 'evaluacion_ia_id');
    }

    // Accessors
    public function getFolioAttribute(): string
    {
        return !empty($this->attributes['consulta_folio'])
            ? $this->attributes['consulta_folio']
            : 'EV-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    public function getRiesgoAttribute(): string
    {
        $nivel = strtolower((string) ($this->consulta->nivel_urgencia ?? ''));

        $alto  = ['rojo', 'naranja', 'urgente', 'alta', 'critico', 'emergencia'];
        $bajo  = ['verde', 'azul', 'baja', 'leve'];

        return match (true) {
            in_array($nivel, $alto, true) => 'ALTO',
            in_array($nivel, $bajo, true) => 'BAJO',
            default                       => 'MEDIO',
        };
    }

    public function getEstadoAttribute(): string
    {
        $nota = $this->notaPsoapp;
        return ($nota && $nota->estado !== 'borrador') ? 'Revisada' : 'Pendiente revisión';
    }

    public function getSintomasArrayAttribute(): array
    {
        $sintomas = $this->attributes['sintomas_detectados'] ?? '';
        if (empty($sintomas)) return [];

        return array_values(array_filter(array_map('trim', explode(',', $sintomas))));
    }

    // Scopes para la búsqueda
    public function scopeVigentes($query)
    {
        return $query->whereIn('id', function ($sub) {
            $sub->selectRaw('MAX(id)')
                ->from('evaluaciones_ia')
                ->groupBy('consulta_id');
        });
    }

    public function scopePaciente($query, ?string $nombre)
    {
        if (!$nombre) return $query;
        return $query->whereHas('consulta.paciente', fn($q) => $q->where('nombre', 'like', "%{$nombre}%"));
    }

    public function scopeRiesgo($query, ?string $riesgo)
    {
        if (!$riesgo) return $query;
        $mapa = [
            'ALTO'  => ['rojo', 'naranja', 'urgente', 'alta', 'critico', 'emergencia'],
            'MEDIO' => ['amarillo', 'normal', 'media', 'moderado'],
            'BAJO'  => ['verde', 'azul', 'baja', 'leve'],
        ];
        $valores = $mapa[strtoupper($riesgo)] ?? [];
        return $query->whereHas('consulta', fn($q) => $q->whereIn('nivel_urgencia', $valores));
    }
}