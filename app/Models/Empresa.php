<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $table = 'configuracion_empresa';

    protected $fillable = [
        'nombre_empresa',
        'razon_social',
        'rfc',
        'logo_url',
        'favicon_url',
        'color_primario',
        'telefono',
        'email',
        'direccion',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_configurado',
        'waha_session',
        'waha_numero_telefono',
        'waha_status',
        'waha_configurado',
        'waha_updated_at',
    ];

    protected $casts = [
        'waha_updated_at' => 'datetime',
    ];

    public function ubicaciones()
    {
        return $this->hasMany(Ubicacion::class);
    }
}