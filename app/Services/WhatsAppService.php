<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public static function enviar($telefono, $mensaje)
    {
        try {

            $instanceId = env('ULTRAMSG_INSTANCE_ID');

            $token = env('ULTRAMSG_TOKEN');

            $url = "https://api.ultramsg.com/$instanceId/messages/chat";

            $response = Http::withoutVerifying()->post($url, [

                'token' => $token,

                'to' => $telefono,

                'body' => $mensaje,
            ]);

            // GUARDAR RESPUESTA EN LOG
            Log::info('Respuesta UltraMsg', [

                'telefono' => $telefono,

                'response' => $response->json()
            ]);

            return $response;

        } catch (\Exception $e) {

            Log::error('Error WhatsApp: ' . $e->getMessage());

            return false;
        }
    }
}