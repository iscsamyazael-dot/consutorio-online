<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WahaController extends Controller
{
    public function iniciarSesion(Request $request)
    {
        $empresa = Empresa::firstOrFail();
        $sessionName = config('database.connections.mysql.database');

        $response = Http::withHeaders([
            'X-Api-Key' => config('services.waha.api_key'),
        ])->post(config('services.waha.base_url') . '/api/sessions', [
            'name' => $sessionName,
            'start' => true,
        ]);

         \Log::info('WAHA iniciarSesion response', [
            'status' => $response->status(),
            'body' => $response->body(),
            'session' => $sessionName,
        ]);

        if (!$response->successful()) {
            return response()->json(['success' => false, 'message' => 'No se pudo crear la sesión en WAHA'], 500);
        }

        $empresa->update([
            'waha_session' => $sessionName,
            'waha_status' => 'SCAN_QR_CODE',
            'waha_updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'session' => $sessionName]);
    }

    public function obtenerQr()
    {
        $empresa = Empresa::firstOrFail();

        $response = Http::withHeaders([
            'X-Api-Key' => config('services.waha.api_key'),
        ])->get(config('services.waha.base_url') . "/api/{$empresa->waha_session}/auth/qr");

        if (!$response->successful()) {
            return response()->json(['success' => false, 'message' => 'No se pudo obtener el QR'], 500);
        }

        // WAHA regresa la imagen en binario; la convertimos a base64 para el frontend
        $base64 = base64_encode($response->body());

        return response()->json([
            'success' => true,
            'qr' => 'data:image/png;base64,' . $base64,
        ]);
    }

    public function estatus()
    {
        $empresa = Empresa::firstOrFail();

        $response = Http::withHeaders([
            'X-Api-Key' => config('services.waha.api_key'),
        ])->get(config('services.waha.base_url') . "/api/sessions/{$empresa->waha_session}");

        $data = $response->json();
        $status = $data['status'] ?? 'UNKNOWN';

        $updateData = ['waha_status' => $status, 'waha_updated_at' => now()];

        \Log::info('WAHA estatus response', ['body' => $response->body()]);

        if ($status === 'WORKING') {
            $updateData['waha_numero_telefono'] = $data['me']['id'] ?? null;
            $updateData['waha_configurado'] = true;
        }

        $empresa->update($updateData);

        return response()->json([
        'success' => true,
        'status' => $status,
        'numero' => $empresa->waha_numero_telefono,
        ]);
    }
}