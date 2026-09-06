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

        $textoBusqueda = $texto;
        if (preg_match('/\(([^)]+)\)/', $texto, $m)) {
            $textoBusqueda = trim(str_ireplace('probable', '', $m[1]));
        }

        $response = Http::withToken($token)
            ->withHeaders([
                'Accept'          => 'application/json',
                'Accept-Language' => 'es',
                'API-Version'     => 'v2',
            ])
            ->get(self::SEARCH_URL, [
                'q'                        => $textoBusqueda,
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

        return $this->reordenarPorRelevancia($resultados, $textoBusqueda);
    }

    /**
     * Palabras que por sí solas no aportan especificidad clínica y causan
     * falsos positivos en el conteo de relevancia (ej. "aguda" hace match
     * tanto con "Pielonefritis aguda" como con "Infección respiratoria
     * aguda", sin ninguna relación clínica real entre ambas).
     */
    private function palabrasIgnorables(): array
    {
        return [
            'de', 'del', 'la', 'las', 'el', 'los', 'en', 'y', 'o', 'con', 'sin',
            'por', 'no', 'un', 'una', 'unos', 'unas',
            'aguda', 'agudo', 'cronica', 'crónica', 'cronico', 'crónico',
            'probable', 'probables', 'especificacion', 'especificación',
            'otro', 'otra', 'otros', 'otras', 'sitio', 'multiples', 'múltiples',
        ];
    }

    private function normalizar(string $texto): string
    {
        $texto = mb_strtolower(trim($texto));
        return str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $texto);
    }

    /**
     * El "score" que regresa la ICD-API con Flexisearch activado no
     * discrimina bien términos clínicamente lejanos que comparten solo
     * palabras genéricas (ej. "aguda", "vías", "altas"). En vez de confiar
     * en ese score, calculamos relevancia propia: cuántas palabras
     * DISTINTIVAS del texto buscado aparecen en el título de cada
     * resultado, más un bono grande si el título contiene la frase casi
     * completa (para que un match de frase gane sobre varios matches de
     * palabras sueltas y comunes).
     */
    private function reordenarPorRelevancia($resultados, string $textoBusqueda): array
    {
        $stopwords = $this->palabrasIgnorables();
        $fraseNormalizada = $this->normalizar($textoBusqueda);

        $palabrasBuscadas = collect(preg_split('/[\s,]+/', $fraseNormalizada))
            ->filter(fn ($p) => mb_strlen($p) > 2 && !in_array($p, $stopwords))
            ->values();

        return $resultados
            ->map(function ($r) use ($palabrasBuscadas, $fraseNormalizada) {
                $tituloNormalizado = $this->normalizar($r['titulo']);

                $coincidencias = $palabrasBuscadas
                    ->filter(fn ($palabra) => str_contains($tituloNormalizado, $palabra))
                    ->count();

                $bonusFrase = str_contains($tituloNormalizado, $fraseNormalizada) ? 50 : 0;

                $r['relevancia'] = $coincidencias + $bonusFrase;
                return $r;
            })
            ->filter(fn ($r) => $r['relevancia'] > 0) // descarta los que no comparten NINGUNA palabra distintiva
            ->sortByDesc('relevancia')
            ->take(6) // top 6 más relevantes
            ->map(fn ($r) => ['codigo' => $r['codigo'], 'titulo' => $r['titulo']]) // sin 'relevancia', el frontend no la necesita
            ->values()
            ->toArray();
    }

}