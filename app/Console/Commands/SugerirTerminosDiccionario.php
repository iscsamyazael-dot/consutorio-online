<?php

namespace App\Console\Commands;

use App\Services\Terminologia\DiccionarioMedico;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Revisa los logs del canal 'anclaje_descartes' (ver config/logging.php) y
 * propone candidatos a agregar al DiccionarioMedico::TERMINOS.
 *
 * IMPORTANTE: este comando SOLO genera un reporte en storage/app/ para
 * revisión humana. Nunca escribe ni modifica DiccionarioMedico.php.
 *
 * Uso:
 *   php artisan diccionario:sugerir-terminos
 *   php artisan diccionario:sugerir-terminos --dias=30 --min=3
 */
class SugerirTerminosDiccionario extends Command
{
    protected $signature = 'diccionario:sugerir-terminos
        {--dias=30 : Ventana de días de logs a revisar}
        {--min=2 : Frecuencia mínima de repeticiones para considerar un término}';

    protected $description = 'Revisa los síntomas descartados por falta de anclaje y propone candidatos al DiccionarioMedico (requiere revisión humana antes de aplicarlos)';

    public function handle(): int
    {
        $dias = (int) $this->option('dias');
        $minFrecuencia = (int) $this->option('min');

        $this->info("Revisando logs de los últimos {$dias} días...");

        $descartes = $this->leerLogsDescartes($dias);

        if (empty($descartes)) {
            $this->info('No se encontraron síntomas descartados en el periodo indicado.');
            return self::SUCCESS;
        }

        // Agrupamos por texto normalizado y contamos frecuencia. Esto evita
        // proponer como "candidato" algo que solo pasó una vez y podría ser
        // ruido de transcripción o un caso aislado sin patrón real.
        $frecuencias = [];
        foreach ($descartes as $item) {
            $clave = mb_strtolower(trim($item['sintoma']));
            if ($clave === '') {
                continue;
            }
            $frecuencias[$clave] = ($frecuencias[$clave] ?? 0) + 1;
        }

        $candidatosFrecuentes = array_filter($frecuencias, fn($f) => $f >= $minFrecuencia);

        if (empty($candidatosFrecuentes)) {
            $this->info("Ningún síntoma descartado alcanzó la frecuencia mínima de {$minFrecuencia} repeticiones.");
            return self::SUCCESS;
        }

        arsort($candidatosFrecuentes);

        $this->info(count($candidatosFrecuentes) . ' términos distintos alcanzaron la frecuencia mínima. Consultando IA para propuestas...');

        $propuestas = $this->consultarPropuestas(array_keys($candidatosFrecuentes));

        $this->guardarReporte($candidatosFrecuentes, $propuestas);

        return self::SUCCESS;
    }

    /**
     * Lee los archivos diarios del canal 'anclaje_descartes' dentro de la
     * ventana de días indicada, y extrae cada entrada "sintoma" registrada
     * por validarAnclajeSintomas() en IAClinicaService.php.
     */
    private function leerLogsDescartes(int $dias): array
    {
        $resultados = [];
        $rutaBase = storage_path('logs');

        for ($i = 0; $i <= $dias; $i++) {
            $fecha = now()->subDays($i)->format('Y-m-d');
            $archivo = "{$rutaBase}/anclaje-descartes-{$fecha}.log";

            if (!file_exists($archivo)) {
                continue;
            }

            $contenido = file_get_contents($archivo);

            // Cada línea de log de Laravel trae el mensaje seguido del
            // contexto en JSON. Buscamos específicamente las líneas de
            // "Síntoma descartado..." y extraemos su bloque JSON.
            preg_match_all(
                '/Síntoma descartado por falta de anclaje.*?(\{.*\})/',
                $contenido,
                $matches
            );

            foreach ($matches[1] as $jsonContexto) {
                $data = json_decode($jsonContexto, true);
                if (is_array($data) && !empty($data['sintoma'])) {
                    $resultados[] = $data;
                }
            }
        }

        return $resultados;
    }

    /**
     * Manda el listado de términos descartados frecuentes a la IA para que
     * proponga, por cada uno, si parece una traducción coloquial->médica
     * legítima que falta en el diccionario (con su propuesta de entrada), o
     * si parece más bien una alucinación/ruido que debería seguir
     * descartándose. Esto es solo generación de propuestas de texto -- no
     * hay ninguna escritura a base de datos ni a archivos de código aquí.
     */
    private function consultarPropuestas(array $terminos): ?array
    {
        $vocabularioActual = DiccionarioMedico::textoReferencia();
        $listaTerminos = implode("\n", array_map(fn($t) => "- \"{$t}\"", $terminos));

        $prompt = "
        Eres un asistente de terminología médica. A continuación tienes una lista de
        \"síntomas\" que un sistema de IA clínica generó pero que fueron DESCARTADOS
        porque no se encontró respaldo léxico en el texto original del paciente ni en
        el diccionario médico actual (coloquial -> médico).

        DICCIONARIO ACTUAL (para que no repitas entradas que ya existen):
        {$vocabularioActual}

        TÉRMINOS DESCARTADOS A REVISAR (con su frecuencia de aparición):
        {$listaTerminos}

        Para cada término, decide UNA de estas dos categorías:

        A) \"agregar_diccionario\": el término parece ser una traducción médica
           razonable de una expresión coloquial que el diccionario aún no cubre.
           Propón la entrada exacta coloquial -> médico que se debería agregar.

        B) \"revisar_manualmente\": el término no encaja claramente en A, podría ser
           una alucinación de la IA, ruido de transcripción, o algo demasiado
           ambiguo para decidir automáticamente. Explica brevemente por qué.

        Responde EXCLUSIVAMENTE con este JSON, sin texto adicional ni Markdown:

        {
          \"propuestas\": [
            {
              \"termino_original\": \"\",
              \"categoria\": \"agregar_diccionario|revisar_manualmente\",
              \"coloquial_propuesto\": \"\",
              \"medico_propuesto\": \"\",
              \"razon\": \"\"
            }
          ]
        }
        ";

        try {
            $response = Http::withToken(config('services.ai.key'))
                ->timeout(120)
                ->post('https://api.deepseek.com/chat/completions', [
                    'model' => 'deepseek-v4-flash',
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                    'response_format' => ['type' => 'json_object'],
                ]);

            if (!$response->successful()) {
                $this->error('Error HTTP al consultar la IA: ' . $response->status());
                return null;
            }

            $data = json_decode($response->json('choices.0.message.content'), true);

            return $data['propuestas'] ?? null;

        } catch (\Exception $e) {
            $this->error('Excepción al consultar la IA: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Guarda el reporte final (frecuencias + propuestas de la IA) como un
     * archivo Markdown legible en storage/app/, para que el médico/admin lo
     * revise y decida manualmente qué entradas agregar a DiccionarioMedico.php.
     * Este método es el único punto de escritura de este comando, y escribe
     * únicamente un reporte de texto -- nunca código PHP ni el diccionario real.
     */
    private function guardarReporte(array $frecuencias, ?array $propuestas): void
    {
        $fecha = now()->format('Y-m-d_H-i');
        $ruta = storage_path("app/diccionario-candidatos_{$fecha}.md");

        $lineas = [
            '# Candidatos a DiccionarioMedico — generado el ' . now()->format('d/m/Y H:i'),
            '',
            '**Este reporte es solo una PROPUESTA. Ningún cambio se aplicó automáticamente al diccionario.**',
            '',
        ];

        if (!$propuestas) {
            $lineas[] = '_No se pudieron obtener propuestas de la IA. Frecuencias detectadas en este periodo:_';
            $lineas[] = '';
            foreach ($frecuencias as $termino => $frecuencia) {
                $lineas[] = "- \"{$termino}\" — {$frecuencia} veces";
            }
        } else {
            foreach ($propuestas as $p) {
                $terminoOriginal = $p['termino_original'] ?? '';
                $frecuencia = $frecuencias[mb_strtolower(trim($terminoOriginal))] ?? '?';
                $categoria = $p['categoria'] ?? 'revisar_manualmente';

                $lineas[] = "## \"{$terminoOriginal}\" ({$frecuencia} veces)";
                $lineas[] = "- **Categoría:** {$categoria}";

                if ($categoria === 'agregar_diccionario') {
                    $coloquial = $p['coloquial_propuesto'] ?? $terminoOriginal;
                    $medico = $p['medico_propuesto'] ?? '';
                    $lineas[] = "- **Propuesta de entrada:** `'{$coloquial}' => '{$medico}'`";
                }

                $lineas[] = "- **Razón:** " . ($p['razon'] ?? 'Sin razón proporcionada.');
                $lineas[] = '';
            }
        }

        file_put_contents($ruta, implode("\n", $lineas));

        $this->info("Reporte guardado en: storage/app/diccionario-candidatos_{$fecha}.md");
    }
}