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
            Log::warning('ICD-11: no se obtuvo token, buscar() aborta.');
            return [];
        }

        $textoBusqueda = $this->extraerTerminoBusqueda($texto);
        $resultados = $this->ejecutarBusquedaEnApi($token, $textoBusqueda);
        Log::info('ICD-11 resultados crudos ANTES de filtrar relevancia:', $resultados->toArray());

        // Si el término extraído (del paréntesis) no dio resultados, y era
        // distinto del texto completo, reintentamos con el texto completo
        // como respaldo -- para no perder el diagnóstico por una extracción
        // de paréntesis que resultó incorrecta o demasiado específica.
        if (empty($resultados) && $textoBusqueda !== $texto) {
            $resultados = $this->ejecutarBusquedaEnApi($token, $texto);
            $textoBusqueda = $texto;
        }

        return $this->reordenarPorRelevancia($resultados, $textoBusqueda);
    }

    /**
     * Decide qué texto usar como búsqueda: si el diagnóstico trae un
     * paréntesis y ese paréntesis contiene la palabra "probable" (patrón
     * típico de la IA: "Término general (término específico probable)"),
     * usamos ese término específico. Si el paréntesis no contiene
     * "probable" (puede ser una causa, una nota, una aclaración -- no un
     * diagnóstico en sí), no confiamos en él y usamos el texto completo.
     */
    private function extraerTerminoBusqueda(string $texto): string
    {
        if (preg_match('/\(([^)]+)\)/', $texto, $m)) {
            $contenidoParentesis = trim($m[1]);

            if (stripos($contenidoParentesis, 'probable') !== false) {
                return trim(str_ireplace('probable', '', $contenidoParentesis));
            }
        }

        return $texto;
    }

    private function ejecutarBusquedaEnApi(string $token, string $textoBusqueda): \Illuminate\Support\Collection
    {
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
            return collect();
        }

        $destinationEntities = $response->json('destinationEntities') ?? [];

        // TEMPORAL: ver qué campos trae cada entidad, sin filtrar nada todavía
        Log::info('ICD-11 entidad CRUDA completa (primeros 3):', array_slice($destinationEntities, 0, 3));

        return collect($destinationEntities)
            ->map(fn ($entidad) => [
                'codigo' => $entidad['theCode'] ?? null,
                'titulo' => strip_tags($entidad['title'] ?? ''),
                'score'  => $entidad['score'] ?? 0, // <-- NUEVO: score real de la OMS
            ])
            ->filter(fn ($e) => $e['codigo'] && $e['titulo'])
            ->values();
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
        // El campo "score" ya viene calculado por la propia API de la OMS
        // (Flexisearch), que entiende sinónimos y terminología médica real
        // -- por ejemplo, ya sabe que "lumbalgia" es sinónimo de "lumbago"
        // aunque el título no contenga esa palabra literal. Por eso ya no
        // recalculamos relevancia nosotros mismos comparando palabras
        // sueltas: eso descartaba sinónimos válidos que la API sí conocía.
        return $resultados
            ->sortByDesc('score')
            ->take(6)
            ->map(fn ($r) => ['codigo' => $r['codigo'], 'titulo' => $r['titulo']])
            ->values()
            ->toArray();
    }
}