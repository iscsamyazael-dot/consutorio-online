<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;

/**
 * Servicio para consultar la API pública y oficial de CIMA
 * (Centro de Información online de Medicamentos - AEMPS, España)
 *
 * Documentación oficial: https://cima.aemps.es/cima/rest/
 * No requiere autenticación ni API key. Es de acceso público.
 */
class CimaService
{
    protected string $baseUrl = 'https://cima.aemps.es/cima/rest';
    protected int $cacheMinutes = 60 * 24; // 24 horas, la info de fichas técnicas cambia poco

    /**
     * Busca medicamentos por nombre comercial o principio activo.
     * Ej: buscarPorNombre('paracetamol')
     */
    public function buscarPorNombre(string $nombre, int $pagina = 1): array
    {
        $cacheKey = 'cima:buscar:' . md5(strtolower($nombre)) . ':' . $pagina;

        return Cache::remember($cacheKey, $this->cacheMinutes * 60, function () use ($nombre, $pagina) {
            try {
                $response = Http::timeout(10)->get("{$this->baseUrl}/medicamentos", [
                    'nombre' => $nombre,
                    'pagina' => $pagina,
                ]);
            } catch (ConnectionException $e) {
                Log::warning('CIMA no disponible al buscar por nombre', [
                    'nombre' => $nombre,
                    'error' => $e->getMessage(),
                ]);
                return ['resultados' => [], 'total' => 0];
            }

            if ($response->failed()) {
                Log::warning('CIMA API error al buscar por nombre', [
                    'nombre' => $nombre,
                    'status' => $response->status(),
                ]);
                return ['resultados' => [], 'total' => 0];
            }

            $data = $response->json();

            return [
                'resultados' => collect($data['resultados'] ?? [])
                    ->map(fn ($med) => $this->normalizarMedicamentoResumen($med))
                    ->values()
                    ->all(),
                'total' => $data['totalFilas'] ?? 0,
            ];
        });
    }

    /**
     * Busca medicamentos filtrando explícitamente por principio activo.
     * Ej: buscarPorPrincipioActivo('ibuprofeno')
     */
    public function buscarPorPrincipioActivo(string $principioActivo, int $pagina = 1): array
    {
        $cacheKey = 'cima:practiv:' . md5(strtolower($principioActivo)) . ':' . $pagina;

        return Cache::remember($cacheKey, $this->cacheMinutes * 60, function () use ($principioActivo, $pagina) {
            try {
                $response = Http::timeout(10)->get("{$this->baseUrl}/medicamentos", [
                    'practiv1' => $principioActivo,
                    'pagina' => $pagina,
                ]);
            } catch (ConnectionException $e) {
                Log::warning('CIMA no disponible al buscar por principio activo', [
                    'principio_activo' => $principioActivo,
                    'error' => $e->getMessage(),
                ]);
                return ['resultados' => [], 'total' => 0];
            }

            if ($response->failed()) {
                return ['resultados' => [], 'total' => 0];
            }

            $data = $response->json();

            return [
                'resultados' => collect($data['resultados'] ?? [])
                    ->map(fn ($med) => $this->normalizarMedicamentoResumen($med))
                    ->values()
                    ->all(),
                'total' => $data['totalFilas'] ?? 0,
            ];
        });
    }

    /**
     * Obtiene el detalle completo de un medicamento por su número de registro.
     * Incluye principios activos, presentaciones, documentos y notas de seguridad.
     */
    public function detalleMedicamento(string $nregistro): ?array
    {
        $cacheKey = 'cima:detalle:' . $nregistro;

        return Cache::remember($cacheKey, $this->cacheMinutes * 60, function () use ($nregistro) {
            try {
                $response = Http::timeout(10)->get("{$this->baseUrl}/medicamento", [
                    'nregistro' => $nregistro,
                ]);
            } catch (ConnectionException $e) {
                Log::warning('CIMA no disponible al obtener detalle', [
                    'nregistro' => $nregistro,
                    'error' => $e->getMessage(),
                ]);
                return null;
            }

            if ($response->failed()) {
                return null;
            }

            return $this->normalizarMedicamentoCompleto($response->json());
        });
    }

    /**
     * Devuelve el contenido HTML de una sección específica de la ficha técnica.
     * $seccion sigue la numeración oficial de CIMA (ej: "4.3" = Contraindicaciones,
     * "4.5" = Interacción con otros medicamentos, "4.8" = Reacciones adversas).
     */
    public function seccionFichaTecnica(string $nregistro, string $seccion): ?string
    {
        $cacheKey = "cima:ft:{$nregistro}:{$seccion}";

        return Cache::remember($cacheKey, $this->cacheMinutes * 60, function () use ($nregistro, $seccion) {
            try {
                $response = Http::timeout(10)
                    ->get("https://cima.aemps.es/cima/dochtml/ft/{$nregistro}/{$seccion}/FichaTecnica.html");
            } catch (ConnectionException $e) {
                Log::warning('CIMA no disponible al obtener sección de ficha técnica', [
                    'nregistro' => $nregistro,
                    'seccion' => $seccion,
                    'error' => $e->getMessage(),
                ]);
                return null;
            }

            return $response->successful() ? $response->body() : null;
        });
    }

    /**
     * Extrae el texto plano de la sección 4.2 (Posología y forma de
     * administración) de la ficha técnica.
     *
     * Se usa para autocompletar los campos "Indicaciones" (por
     * medicamento) y "Recomendación general" en RecetaInteligente.vue
     * cuando el médico agrega un medicamento desde el buscador CIMA.
     * El texto siempre queda editable por el médico antes de guardar
     * la receta.
     */
    public function posologia(string $nregistro): ?string
    {
        $html = $this->seccionFichaTecnica($nregistro, '4.2');

        if (!$html) {
            return null;
        }

        // Quitamos etiquetas HTML, decodificamos entidades (&aacute;, &nbsp;, etc.)
        // y colapsamos espacios/saltos de línea repetidos para dejar un texto
        // limpio y legible en un textarea/input.
        $texto = strip_tags($html);
        $texto = html_entity_decode($texto, ENT_QUOTES, 'UTF-8');
        $texto = preg_replace('/\s+/u', ' ', $texto);
        $texto = trim($texto);

        return $texto !== '' ? $texto : null;
    }

    /**
     * Atajo pensado para RecetaInteligente.vue: dado un texto libre
     * ("Paracetamol 500 mg"), extrae el nombre base y devuelve
     * la ficha resumida del primer resultado más relevante.
     */
    public function buscarParaReceta(string $textoLibre): ?array
    {
        // Nos quedamos solo con la parte alfabética inicial (nombre del fármaco),
        // ignorando dosis/unidades tipo "500 mg", "20 ml", etc.
        preg_match('/^[\p{L}\s]+/u', trim($textoLibre), $matches);
        $nombreBase = trim($matches[0] ?? $textoLibre);

        $resultado = $this->buscarPorNombre($nombreBase);

        if (empty($resultado['resultados'])) {
            return null;
        }

        $primero = $resultado['resultados'][0];

        return $this->detalleMedicamento($primero['nregistro']);
    }

    private function normalizarMedicamentoResumen(array $med): array
    {
        // El endpoint de listado (/medicamentos) no incluye principiosActivos;
        // eso solo viene en el detalle (/medicamento?nregistro=...).
        return [
            'nregistro' => $med['nregistro'] ?? null,
            'nombre' => $med['nombre'] ?? null,
            'laboratorio' => $med['labtitular'] ?? null,
            'generico' => $med['generico'] ?? false,
            'comercializado' => $med['comerc'] ?? false,
            'requiere_receta' => $med['receta'] ?? null,
        ];
    }

    private function normalizarMedicamentoCompleto(array $med): array
    {
        return [
            'nregistro' => $med['nregistro'] ?? null,
            'nombre' => $med['nombre'] ?? null,
            'laboratorio' => $med['labtitular'] ?? null,
            'comercializado' => $med['comerc'] ?? false,
            'requiere_receta' => $med['receta'] ?? null,
            'principios_activos' => collect($med['principiosActivos'] ?? [])
                ->map(fn ($p) => [
                    'nombre' => $p['nombre'] ?? null,
                    'cantidad' => $p['cantidad'] ?? null,
                    'unidad' => $p['unidad'] ?? null,
                ])->all(),
            'via_administracion' => collect($med['viasAdministracion'] ?? [])
                ->pluck('nombre')->all(),
            'atc' => collect($med['atcs'] ?? [])
                ->map(fn ($a) => ['codigo' => $a['codigo'] ?? null, 'nombre' => $a['nombre'] ?? null])
                ->all(),
            'documentos' => collect($med['docs'] ?? [])
                ->map(fn ($d) => [
                    'tipo' => $this->tipoDocumento($d['tipo'] ?? null),
                    'url' => $d['urlHtml'] ?? $d['url'] ?? null,
                ])->all(),
            'notas_seguridad' => collect($med['notas'] ?? [])
                ->map(fn ($n) => [
                    'asunto' => $n['asunto'] ?? null,
                    'fecha' => $n['fecha'] ?? null,
                    'url' => $n['url'] ?? null,
                ])->all(),
        ];
    }

    private function tipoDocumento(?int $tipo): string
    {
        return match ($tipo) {
            1 => 'Ficha Técnica',
            2 => 'Prospecto',
            3 => 'Informe Público de Evaluación',
            4 => 'Plan de Gestión de Riesgos',
            default => 'Documento',
        };
    }
}