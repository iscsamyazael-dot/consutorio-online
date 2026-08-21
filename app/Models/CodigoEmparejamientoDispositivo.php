<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodigoEmparejamientoDispositivo extends Model
{
    protected $table = 'codigos_emparejamiento_dispositivo';

    protected $fillable = [
        'codigo',
        'nombre_dispositivo',
        'tipo',
        'expira_en',
        'usado_en',
    ];

    protected $casts = [
        'expira_en' => 'datetime',
        'usado_en'  => 'datetime',
    ];

    // ──────────────────────────────────────────
    // Scopes útiles
    // ──────────────────────────────────────────

    /**
     * Códigos que aún se pueden usar: no usados y no expirados.
     * Reutilizado tanto en el do-while del controlador (validar
     * colisiones) como en emparejar() (validar el código recibido).
     */
    public function scopeActivo($query)
    {
        return $query->whereNull('usado_en')
            ->where('expira_en', '>', now());
    }

    // ──────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────

    public function estaActivo(): bool
    {
        return is_null($this->usado_en) && $this->expira_en->isFuture();
    }
}