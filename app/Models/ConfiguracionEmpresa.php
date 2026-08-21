<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionEmpresa extends Model
{
    protected $table = 'configuracion_empresa';

    protected $fillable = [
        'nombre_empresa', 'razon_social', 'rfc', 'logo_url', 'favicon_url',
        'color_primario', 'telefono', 'email', 'direccion',
        'mail_host', 'mail_port', 'mail_username', 'mail_password',
        'mail_encryption', 'mail_configurado',
    ];

    protected $casts = [
        'mail_password' => 'encrypted',
    ];
}