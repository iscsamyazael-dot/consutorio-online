<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionEmpresa;
use Illuminate\Http\Request;

class ConfiguracionEmpresaController extends Controller
{
    /**
     * Devuelve la configuración actual (para que el formulario
     * sepa si ya hay algo guardado, sin mostrar la contraseña).
     */
    public function show()
    {
        $empresa = ConfiguracionEmpresa::firstOrFail();

        return response()->json($empresa->makeHidden('mail_password'));
    }

    /**
     * Guarda la configuración SMTP que el admin llenó en su formulario.
     */
    public function guardarConfigCorreo(Request $request)
    {
        $request->validate([
            'mail_host' => 'nullable|string',
            'mail_port' => 'nullable|string',
            'mail_username' => 'required|email',
            'mail_password' => 'required|string',
            'mail_encryption' => 'nullable|in:tls,ssl',
        ]);

        $empresa = ConfiguracionEmpresa::firstOrFail();

        $empresa->update([
            'mail_host' => $request->mail_host ?? 'smtp.gmail.com',
            'mail_port' => $request->mail_port ?? '587',
            'mail_username' => $request->mail_username,
            'mail_password' => $request->mail_password,
            'mail_encryption' => $request->mail_encryption ?? 'tls',
            'mail_configurado' => true,
            'email' => $empresa->email ?: $request->mail_username,
        ]);

        return response()->json(['success' => true]);
    }
}