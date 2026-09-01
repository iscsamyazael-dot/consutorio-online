<?php

namespace App\Services;

use App\Models\Empresa;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TenantMailConfigurator
{
    public static function enviarCorreoConQr($paciente, array $archivos)
    {
        $dbNameActual = \DB::connection()->getDatabaseName();
        Log::info('BASE DE DATOS ACTIVA EN EL RUNTIME: ' . $dbNameActual);

        $empresa = Empresa::first();

        if (!$empresa || empty($empresa->mail_password)) {
            throw new \RuntimeException('El consultorio no tiene token de Google configurado en la BD: ' . $dbNameActual);
        }

        Log::info('EMPRESA ENCONTRADA ID: ' . $empresa->id);

        $rawPassword = $empresa->mail_password;
        $decrypted = null;

        try {
            $decrypted = decrypt($rawPassword);
        } catch (\Exception $e) {
            $decrypted = $rawPassword;
        }

        if (empty($decrypted)) {
            $decrypted = $rawPassword;
        }

        $accessToken = null;
        $refreshToken = null;

        // Verificamos si lo que está guardado es un JSON válido
        $tokenData = json_decode($decrypted, true);

        if (is_array($tokenData)) {
            // Socialite guarda la respuesta de Google donde el token suele estar en 'access_token' o 'token'
            $accessToken = $tokenData['access_token'] ?? $tokenData['token'] ?? null;
            $refreshToken = $tokenData['refresh_token'] ?? null;
            $expiresIn = $tokenData['expires_in'] ?? 3600;
            $created = $tokenData['created'] ?? time();

            // Si expiró y tenemos refresh_token, lo renovamos
            if ($accessToken && (time() - $created >= ($expiresIn - 120)) && $refreshToken) {
                $responseRefresh = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                    'client_id'     => config('services.google.client_id'),
                    'client_secret' => config('services.google.client_secret'),
                    'refresh_token' => $refreshToken,
                    'grant_type'    => 'refresh_token',
                ]);

                if ($responseRefresh->successful()) {
                    $newData = $responseRefresh->json();
                    $tokenData['access_token'] = $newData['access_token'];
                    $tokenData['created'] = time();

                    $empresa->update([
                        'mail_password' => encrypt(json_encode($tokenData))
                    ]);

                    $accessToken = $tokenData['access_token'];
                }
            }
        } else {
            // Si se guardó como texto plano directamente
            $accessToken = trim($decrypted);
        }

        if (empty($accessToken)) {
            throw new \RuntimeException('No se pudo extraer un access_token válido de la base de datos.');
        }

        Log::info('Token que se enviará a Google (primeros caracteres): ' . substr($accessToken, 0, 10) . '...');

        $remitente = $empresa->mail_username; 
        $nombreConsultorio = $empresa->nombre_empresa ?? 'Consultorio Médico';

        $asunto = "Tu Código QR de Acceso - " . $nombreConsultorio;
        $asuntoCodificado = mb_encode_mimeheader($asunto, "UTF-8", "B", "\r\n");
        $nombreCodificado = mb_encode_mimeheader($nombreConsultorio, "UTF-8", "B", "\r\n");
        $destinatario = $paciente->email;

        $boundary = md5(time());              // externo: mixed (cuerpo + adjuntos)
        $boundaryAlt = md5(time() . 'alt');   // interno: alternative (texto + html)

        $mimeMensaje = "From: {$nombreCodificado} <{$remitente}>\r\n";
        $mimeMensaje .= "To: {$destinatario}\r\n";
        $mimeMensaje .= "Subject: {$asuntoCodificado}\r\n";
        $mimeMensaje .= "MIME-Version: 1.0\r\n";
        $mimeMensaje .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n\r\n";

        // --- Bloque de cuerpo (texto + HTML), anidado dentro del mixed ---
        $mimeMensaje .= "--{$boundary}\r\n";
        $mimeMensaje .= "Content-Type: multipart/alternative; boundary=\"{$boundaryAlt}\"\r\n\r\n";

        // Versión texto plano
        $mimeMensaje .= "--{$boundaryAlt}\r\n";
        $mimeMensaje .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
        $mimeMensaje .= "Hola {$paciente->nombre},\n\nAdjunto encontraras tu expediente y tu codigo QR para tu consulta en {$nombreConsultorio}.\n\nPresentalo al llegar a la clinica para tu registro.\n\nSaludos cordiales.\r\n\r\n";

        // Versión HTML
        $mimeMensaje .= "--{$boundaryAlt}\r\n";
        $mimeMensaje .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
        $mimeMensaje .= "Hola {$paciente->nombre},<br><br>Adjunto encontrarás tu expediente y tu código QR para tu consulta en <b>{$nombreConsultorio}</b>.<br><br>Preséntalo al llegar a la clínica para tu registro.<br><br>Saludos cordiales.\r\n\r\n";

        $mimeMensaje .= "--{$boundaryAlt}--\r\n\r\n";   // cierra el multipart/alternative

        // --- Adjunto 1: PDF del expediente ---
        $pdfContenido = base64_encode(file_get_contents($archivos['pdf']));
        $mimeMensaje .= "--{$boundary}\r\n";
        $mimeMensaje .= "Content-Type: application/pdf; name=\"expediente-paciente.pdf\"\r\n";
        $mimeMensaje .= "Content-Transfer-Encoding: base64\r\n";
        $mimeMensaje .= "Content-Disposition: attachment; filename=\"expediente-paciente.pdf\"\r\n\r\n";
        $mimeMensaje .= chunk_split($pdfContenido) . "\r\n";

        // --- Adjunto 2: PNG del QR suelto ---
        $qrContenido = base64_encode(file_get_contents($archivos['qr']));
        $mimeMensaje .= "--{$boundary}\r\n";
        $mimeMensaje .= "Content-Type: image/png; name=\"qr-paciente.png\"\r\n";
        $mimeMensaje .= "Content-Transfer-Encoding: base64\r\n";
        $mimeMensaje .= "Content-Disposition: attachment; filename=\"qr-paciente.png\"\r\n\r\n";
        $mimeMensaje .= chunk_split($qrContenido) . "\r\n";

        $mimeMensaje .= "--{$boundary}--";   // cierra el multipart/mixed externo

        // Codificamos el mensaje en URL-safe Base64 para la API de Gmail
        $encodedMessage = rtrim(strtr(base64_encode($mimeMensaje), '+/', '-_'), '=');

        // 3. Consumimos directamente la API de Gmail
        $response = Http::withToken($accessToken)
            ->post("https://gmail.googleapis.com/gmail/v1/users/me/messages/send", [
                'raw' => $encodedMessage
            ]);

        if ($response->failed()) {
            Log::error('Error en API de Gmail: ' . $response->body());

            // Si es 401 y tenemos refresh_token, intentamos refrescar UNA vez y reintentar
            if ($response->status() === 401 && $refreshToken) {
                Log::info('Token expirado (401), intentando refresh forzado...');

                $responseRefresh = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                    'client_id'     => config('services.google.client_id'),
                    'client_secret' => config('services.google.client_secret'),
                    'refresh_token' => $refreshToken,
                    'grant_type'    => 'refresh_token',
                ]);

                if ($responseRefresh->successful()) {
                    $newData = $responseRefresh->json();
                    $tokenData['access_token'] = $newData['access_token'];
                    $tokenData['created'] = time();

                    $empresa->update([
                        'mail_password' => encrypt(json_encode($tokenData))
                    ]);

                    $accessToken = $tokenData['access_token'];

                    // Reintentamos el envío una sola vez con el token nuevo
                    $response = Http::withToken($accessToken)
                        ->post("https://gmail.googleapis.com/gmail/v1/users/me/messages/send", [
                            'raw' => $encodedMessage
                        ]);

                    if ($response->successful()) {
                        return true;
                    }

                    Log::error('Error en API de Gmail tras reintento: ' . $response->body());
                } else {
                    Log::error('No se pudo refrescar el token tras 401: ' . $responseRefresh->body());
                }
            }

            throw new \RuntimeException('No se pudo enviar el correo mediante la API de Google: ' . $response->body());
        }
        return true;
    }
}