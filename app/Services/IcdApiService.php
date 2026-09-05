<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class IcdApiService
{
    private const TOKEN_URL = 'https://icdaccessmanagement.who.int/connect/token';
    private const SEARCH_URL = 'https://id.who.int/icd/release/11/2024-01/mms/search';

    /**
     * Obtiene el token OAuth2 (client_credentials) y lo cachea hasta
     * poco antes de que expire, para no pedir uno nuevo en cada búsqueda.
     */
    private function obtenerToken(): ?string
    {
        return Cache::remember('icd11_access_token', 3300, function () {
            $response = Http::asForm()->post(self::TOKEN_URL, [
                'client_id'     => config('services.icd.client_id'),
                'client_secret' => config('services.icd.client_secret'),
                'scope'         => 'icdapi_access',
                'grant_type'    => 'client_credentials',
            ]);

            if (!$response->successful()) {
                Log::error('Error al obtener token de ICD-API: ' . $response->body());
                return null;
            }

            return $response->json('access_token');
        });
    }

    /**
     * Busca en la clasificación ICD-11 (linearización MMS, la que usa
     * códigos) coincidencias para el texto libre que escribe el médico.
     * Devuelve un arreglo simplificado [{codigo, titulo}, ...].
     */
    public function buscar(string $texto): array
    {
        $token = $this->obtenerToken();

        if (!$token) {
            return [];
        }

        $response = Http::withToken($token)
            ->withHeaders([
                'Accept'          => 'application/json',
                'Accept-Language' => 'es',
                'API-Version'     => 'v2',
            ])
            ->get(self::SEARCH_URL, [
                'q'                        => $texto,
                'flatResults'              => 'true',
                'useFlexisearch'           => 'true',
                'medicalCodingMode'        => 'true',
            ]);

        if (!$response->successful()) {
            Log::error('Error al buscar en ICD-API: ' . $response->body());
            return [];
        }

        $destinationEntities = $response->json('destinationEntities') ?? [];
        
        $resultados = collect($destinationEntities)
            ->map(function ($entidad) {
                return [
                    'codigo' => $entidad['theCode'] ?? null,
                    'titulo' => strip_tags($entidad['title'] ?? ''),
                ];
            })
            ->filter(fn ($e) => $e['codigo'] && $e['titulo'])
            ->values();
        
        // El "score" que regresa la ICD-API con Flexisearch activado no
        // discrimina bien términos clínicamente lejanos que comparten una
        // sola palabra (ej. "Hipertensión neonatal" para una búsqueda de
        // "Hipertensión arterial sistémica"). En vez de confiar en ese score,
        // calculamos relevancia propia: cuántas palabras del texto buscado
        // aparecen también en el título de cada resultado. A mayor
        // coincidencia de palabras, más relevante.
        $palabrasBuscadas = collect(preg_split('/\s+/', mb_strtolower($texto)))
            ->filter(fn ($p) => mb_strlen($p) > 2) // ignoramos conectores muy cortos
            ->values();

        return $resultados
            ->map(function ($r) use ($palabrasBuscadas) {
                $tituloNormalizado = mb_strtolower($r['titulo']);
                $coincidencias = $palabrasBuscadas
                    ->filter(fn ($palabra) => str_contains($tituloNormalizado, $palabra))
                    ->count();

                $r['relevancia'] = $coincidencias;
                return $r;
            })
            ->filter(fn ($r) => $r['relevancia'] > 0) // descarta los que no comparten NINGUNA palabra
            ->sortByDesc('relevancia')
            ->take(6) // top 6 más relevantes
            ->map(fn ($r) => ['codigo' => $r['codigo'], 'titulo' => $r['titulo']]) // sin 'relevancia', el frontend no la necesita
            ->values()
            ->toArray();
    }

}