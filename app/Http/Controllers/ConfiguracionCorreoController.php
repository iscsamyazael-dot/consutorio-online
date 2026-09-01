<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class ConfiguracionCorreoController extends Controller
{
    // 1. Devuelve el estatus actual para pintar la interfaz en Vue.js
    public function estatus()
    {
        $empresa = Empresa::first();

        return response()->json([
            'conectado' => !empty($empresa->mail_username),
            'email' => $empresa->mail_username ?? ''
        ]);
    }

    // 2. Redirige a Google pidiendo acceso offline (para obtener el refresh_token)
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->scopes(['https://www.googleapis.com/auth/gmail.send'])
            ->with([
                'access_type' => 'offline',
                'prompt' => 'consent' // Fuerza a Google a entregar el token de actualización
            ])
            ->redirect();
    }

    // 3. Recibe la respuesta de Google y ACTUALIZA la fila existente
    public function handleGoogleCallback()
    {
        try {
            $userGoogle = Socialite::driver('google')->stateless()->user();

            $tokenData = [
                'access_token'  => $userGoogle->token,
                'refresh_token' => $userGoogle->refreshToken,
                'expires_in'    => $userGoogle->expiresIn,
                'created'       => time(),
            ];

            \Log::info('Token armado manualmente: ' . json_encode([
                'access_token_presente' => !empty($tokenData['access_token']),
                'refresh_token_presente' => !empty($tokenData['refresh_token']),
                'expires_in' => $tokenData['expires_in'],
            ]));

            $empresa = Empresa::first();

            if ($empresa) {
                $empresa->update([
                    'mail_username'   => $userGoogle->getEmail(),
                    'mail_password'   => encrypt(json_encode($tokenData)),
                    'mail_host'       => 'smtp.gmail.com',
                    'mail_port'       => 587,
                    'mail_encryption' => 'tls',
                    'mail_configurado'=> 1,
                ]);
            }

            return redirect('/vincular-correo')->with('success', 'Correo vinculado correctamente.');

        } catch (\Throwable $e) {
            \Log::error('Error en Google Callback: ' . $e->getMessage());
            return redirect('/vincular-correo')->with('error', 'No se pudo vincular la cuenta.');
        }
    }

    // 4. Desconecta la cuenta limpiando o vaciando los campos de la fila existente
    public function desconectar()
    {
        $empresa = Empresa::first();

        if ($empresa) {
            $empresa->update([
                'mail_username' => null,
                'mail_password' => null,
                'mail_host'     => null,
                'mail_port'     => null,
                'mail_encryption' => null,
                'mail_configurado' => 0,
            ]);
        }

        return response()->json(['message' => 'Cuenta desconectada con éxito']);
    }
}