<?php

namespace App\Services;

use App\Models\ConfiguracionEmpresa;
use Illuminate\Support\Facades\Config;

class TenantMailConfigurator
{
    public static function aplicar(): void
    {
        $empresa = ConfiguracionEmpresa::first();

        if (!$empresa || !$empresa->mail_configurado || empty($empresa->mail_username)) {
            throw new \RuntimeException('El consultorio no tiene correo configurado.');
        }

        Config::set('mail.mailers.smtp.host', $empresa->mail_host);
        Config::set('mail.mailers.smtp.port', $empresa->mail_port);
        Config::set('mail.mailers.smtp.username', $empresa->mail_username);
        Config::set('mail.mailers.smtp.password', $empresa->mail_password);
        Config::set('mail.mailers.smtp.encryption', $empresa->mail_encryption ?? 'tls');
        Config::set('mail.from.address', $empresa->email);
        Config::set('mail.from.name', $empresa->nombre_empresa);
        Config::set('mail.default', 'smtp');
    }
}