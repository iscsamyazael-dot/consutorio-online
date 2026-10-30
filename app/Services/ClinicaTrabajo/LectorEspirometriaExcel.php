<?php

namespace App\Services\ClinicaTrabajo;

use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

/**
 * Lee la tabla de resultados de un informe de espirometría exportado a Excel
 * (equipo ndd EasyOne) y devuelve la misma estructura que
 * IAClinicaTrabajoService::analizarEspirometria, sin usar IA.
 *
 * Solo importa lo que reporta el equipo (Pred, LLN, pruebas, Mejor y %Pred).
 * FEV1/FVC se conserva en porcentaje (73.8), igual que en el informe. Sin
 * modificar ningún valor, genera advertencias si algo no cuadra con los cálculos.
 */
class LectorEspirometriaExcel
{
    /** Claves internas, en el mismo orden que usa el formulario. */
    private const CLAVES = ['fvc', 'fev1', 'fev1_fvc', 'fef25_75', 'pef', 'fet', 'fivc', 'pif', 'eotv', 'bev'];

    /** Etiqueta del Excel (sin unidad) => clave interna. */
    private const ETIQUETAS = [
        'FVC' => 'fvc',
        'FEV1' => 'fev1',
        'FEV1/FVC' => 'fev1_fvc',
        'FEF25-75' => 'fef25_75',
        'PEF' => 'pef',
        'FET' => 'fet',
        'FIVC' => 'fivc',
        'PIF' => 'pif',
        'EOTV' => 'eotv',
        'BEV' => 'bev',
    ];

    /** Clave interna => campo dentro de cada maniobra (mismo nombre que usa el PDF). */
    private const CAMPO_MANIOBRA = [
        'fvc' => 'fvc_l',
        'fev1' => 'fev1_l',
        'fev1_fvc' => 'fev1_fvc_ratio',
        'fef25_75' => 'fef25_75',
        'pef' => 'pef_l_s',
        'fet' => 'fet_s',
        'fivc' => 'fivc_l',
        'pif' => 'pif_l_s',
        'eotv' => 'eotv_l',
        'bev' => 'bev_l',
    ];

    /** Parámetros con Puntuación Z en el informe. */
    private const CLAVES_Z = ['fvc', 'fev1', 'fev1_fvc', 'fef25_75', 'pef', 'fivc'];

    /** Orden de calidad del estándar (de mejor a peor); U (usable) queda antes de F. */
    private const ORDEN_CALIDAD = ['A', 'B', 'C', 'D', 'E', 'U', 'F'];

    /** Parámetros cuyo Mejor debe ser el máximo de las pruebas. */
    private const PARAMETROS_MAXIMO = ['fvc', 'fev1', 'fef25_75', 'pef', 'fivc', 'pif'];

    /** Parámetros con %Pred calculable (Mejor / Pred). */
    private const PARAMETROS_PCT = ['fvc', 'fev1', 'fev1_fvc', 'fef25_75', 'pef', 'fivc'];

    private const TOLERANCIA_RATIO = 0.3; // puntos porcentuales
    private const TOLERANCIA_MEJOR = 0.005;
    private const TOLERANCIA_PCT = 1;
    private const TOLERANCIA_FET = 0.05;

    /**
     * @return array Estructura lista para el formulario, con la clave 'advertencias'.
     * @throws RuntimeException si el archivo no tiene la tabla de espirometría.
     */
    public function leer(string $rutaCompleta): array
    {
        $libro = IOFactory::load($rutaCompleta);
        $filas = $libro->getActiveSheet()->toArray(null, false, false, false);

        [$indiceEncabezado, $encabezado] = $this->localizarEncabezado($filas);
        $columnas = $this->mapearColumnas($encabezado);

        $valores = $this->leerFilasDeTabla($filas, $indiceEncabezado, $columnas);
        if ($valores === []) {
            throw new RuntimeException('No se encontraron filas de espirometría en el Excel.');
        }

        $maniobras = $this->leerManiobras($filas, $indiceEncabezado, $columnas);

        $resultado = $this->construirResultado($valores, $maniobras);
        $resultado += $this->leerCalidadEInterpretacion($filas);
        $resultado['maniobras'] = array_values($maniobras);
        $resultado['advertencias'] = $this->validar($valores, $maniobras, $resultado);
        $resultado['fecha_prueba'] = $this->leerCampoEtiqueta($filas, 'Fecha del test');
        $resultado['fumador_texto'] = $this->leerCampoEtiqueta($filas, 'Fumador');

        return $resultado;
    }

    /**
     * Busca la fila de encabezado: la que tiene "Parámetro" y "Mejor".
     */
    private function localizarEncabezado(array $filas): array
    {
        foreach ($filas as $indice => $fila) {
            $textos = array_map(fn ($c) => $this->normalizarTexto($c), $fila);
            if (in_array('Parámetro', $textos, true) && in_array('Mejor', $textos, true)) {
                return [$indice, $fila];
            }
        }

        throw new RuntimeException('No se encontró el encabezado de la tabla (Parámetro / Mejor).');
    }

    /**
     * Ubica en qué columna está cada dato del encabezado, por su nombre.
     * Así no depende del orden ni del número de pruebas.
     */
    private function mapearColumnas(array $encabezado): array
    {
        $columnas = ['mejor' => null, 'lln' => null, 'pred' => null, 'pct' => null, 'z' => null, 'pruebas' => []];

        foreach ($encabezado as $col => $celda) {
            $texto = $this->normalizarTexto($celda);

            match (true) {
                $texto === 'Mejor' => $columnas['mejor'] = $col,
                $texto === 'LLN' => $columnas['lln'] = $col,
                $texto === 'Pred' => $columnas['pred'] = $col,
                $texto === '%Pred' => $columnas['pct'] = $col,
                preg_match('/^Puntuaci[oó]n Z$/u', $texto) === 1 => $columnas['z'] = $col,
                preg_match('/^Prueba (\d+)$/u', $texto, $m) === 1 => $columnas['pruebas'][(int) $m[1]] = $col,
                default => null,
            };
        }

        if ($columnas['mejor'] === null) {
            throw new RuntimeException('El encabezado no tiene la columna Mejor.');
        }

        ksort($columnas['pruebas']);

        return $columnas;
    }

    /**
     * Lee las filas de parámetros (FVC, FEV1, ...) justo debajo del encabezado.
     * Devuelve por clave: ['mejor', 'lln', 'pred', 'pct'] ya convertidos.
     */
    private function leerFilasDeTabla(array $filas, int $indiceEncabezado, array $columnas): array
    {
        $valores = [];
        $limite = min(count($filas), $indiceEncabezado + 20);

        for ($i = $indiceEncabezado + 1; $i < $limite; $i++) {
            $clave = $this->claveDeEtiqueta($filas[$i][0] ?? null);
            if ($clave === null || isset($valores[$clave])) {
                continue;
            }

            $fila = $filas[$i];
            $valores[$clave] = [
                'mejor' => $this->celda($fila, $columnas['mejor'])['valor'],
                'lln' => $this->celda($fila, $columnas['lln'])['valor'],
                'pred' => $this->celda($fila, $columnas['pred'])['valor'],
                'pct' => $this->celda($fila, $columnas['pct'])['valor'],
                'z' => $this->celda($fila, $columnas['z'])['valor'],
            ];
        }

        // FIVC toma Pred y LLN de FVC, como hace el formulario.
        if (isset($valores['fivc'], $valores['fvc'])) {
            $valores['fivc']['pred'] ??= $valores['fvc']['pred'];
            $valores['fivc']['lln'] ??= $valores['fvc']['lln'];
        }

        return $valores;
    }

    /** Valor de la columna C de la fila cuya primera celda coincide con la etiqueta. */
    private function leerCampoEtiqueta(array $filas, string $etiqueta): mixed
    {
        foreach ($filas as $fila) {
            if ($this->normalizarTexto($fila[0] ?? null) === $etiqueta) {
                return $fila[2] ?? null;
            }
        }
        return null;
    }

    /**
     * Lee las pruebas (Prueba 3, 4, 1, ...) de cada parámetro.
     * Devuelve [numero => ['numero' => n, campo => valor, ..., 'marcados' => [...]]].
     */
    private function leerManiobras(array $filas, int $indiceEncabezado, array $columnas): array
    {
        $maniobras = [];
        foreach (array_keys($columnas['pruebas']) as $numero) {
            $maniobras[$numero] = ['numero' => $numero, 'marcados' => []];
            foreach (self::CAMPO_MANIOBRA as $campo) {
                $maniobras[$numero][$campo] = null;
            }
        }

        $limite = min(count($filas), $indiceEncabezado + 20);

        for ($i = $indiceEncabezado + 1; $i < $limite; $i++) {
            $clave = $this->claveDeEtiqueta($filas[$i][0] ?? null);
            if ($clave === null) {
                continue;
            }

            $campo = self::CAMPO_MANIOBRA[$clave];

            foreach ($columnas['pruebas'] as $numero => $col) {
                $celda = $this->celda($filas[$i], $col);
                $valor = $celda['valor'];

                $maniobras[$numero][$campo] ??= null;
                if ($maniobras[$numero][$campo] === null) {
                    $maniobras[$numero][$campo] = $valor;
                    if ($celda['marcado']) {
                        $maniobras[$numero]['marcados'][$campo] = true;
                    }
                }
            }
        }

        ksort($maniobras);

        return $maniobras;
    }

    /**
     * Arma las claves que consume el formulario, con la misma nomenclatura
     * que IAClinicaTrabajoService::normalizarEspirometria.
     */
    private function construirResultado(array $valores, array $maniobras): array
    {
        $resultado = [];

        foreach (self::CLAVES as $clave) {
            $fila = $valores[$clave] ?? ['mejor' => null, 'lln' => null, 'pred' => null, 'pct' => null, 'z' => null];

            $resultado["{$clave}_pred"] = $fila['pred'];
            $resultado["{$clave}_lln"] = $fila['lln'];
            $resultado["mejor_{$clave}"] = $fila['mejor'];
            $resultado["{$clave}_pct"] = $fila['pct'] !== null ? (int) round($fila['pct']) : null;

            if (in_array($clave, self::CLAVES_Z, true)) {
                $resultado["{$clave}_z"] = $fila['z'];
            }
        }

        return $resultado;
    }

    /**
     * Lee calidad de sesión, varianzas e interpretación de las filas al final de la tabla.
     */
    private function leerCalidadEInterpretacion(array $filas): array
    {
        $calidad = $this->ultimoTexto($filas, 'Calidad de la sesión');
        $interpretacion = $this->ultimoTexto($filas, 'Interpretación del sistema');

        $resultado = [
            'calidad_sesion' => null,
            'calidad_fev1' => null,
            'calidad_fvc' => null,
            'fev1_var' => null,
            'fvc_var' => null,
            'interpretacion' => $interpretacion,
        ];

        if ($calidad === null) {
            return $resultado;
        }

        // Formato: "FEV1 - A, FVC - A (FVC Var=0.07L (1.6%); FEV1 Var=0.09L (2.7%))"
        if (preg_match('/FEV1\s*-\s*([A-Z])/u', $calidad, $m1)) {
            $resultado['calidad_fev1'] = $m1[1];
        }
        if (preg_match('/FVC\s*-\s*([A-Z])/u', $calidad, $m2)) {
            $resultado['calidad_fvc'] = $m2[1];
        }

        // La calidad general es la peor de FEV1 y FVC (el estándar las gradúa por separado).
        $grados = array_values(array_filter([$resultado['calidad_fev1'], $resultado['calidad_fvc']]));
        if ($grados !== []) {
            $resultado['calidad_sesion'] = $this->peorCalidad($grados);
        }

        if (preg_match('/FVC Var=([\-\d.]+)L/u', $calidad, $m3)) {
            $resultado['fvc_var'] = (float) $m3[1];
        }
        if (preg_match('/FEV1 Var=([\-\d.]+)L/u', $calidad, $m4)) {
            $resultado['fev1_var'] = (float) $m4[1];
        }

        return $resultado;
    }

    /**
     * Compara los datos contra los cálculos. Solo advierte; no cambia valores.
     */
    private function validar(array $valores, array $maniobras, array $resultado): array
    {
        $advertencias = [];

        // 1. FEV1/FVC del informe contra FEV1 / FVC de cada prueba.
        foreach ($maniobras as $m) {
            if ($m['fev1_l'] === null || $m['fvc_l'] === null || $m['fev1_fvc_ratio'] === null || $m['fvc_l'] == 0) {
                continue;
            }
            $calculado = $m['fev1_l'] / $m['fvc_l'] * 100;
            if (abs($calculado - $m['fev1_fvc_ratio']) > self::TOLERANCIA_RATIO) {
                $advertencias[] = sprintf(
                    'Prueba %d: FEV1/FVC del informe (%.1f) no coincide con FEV1 / FVC (%.1f).',
                    $m['numero'],
                    $m['fev1_fvc_ratio'],
                    $calculado
                );
            }
        }

        // 2. El Mejor debe ser el máximo de las pruebas (parámetros de volumen y flujo).
        foreach (self::PARAMETROS_MAXIMO as $clave) {
            $mejor = $valores[$clave]['mejor'] ?? null;
            $campo = self::CAMPO_MANIOBRA[$clave];
            $pruebas = array_filter(array_column($maniobras, $campo), fn ($v) => $v !== null);

            if ($mejor === null || $pruebas === []) {
                continue;
            }
            $maximo = max($pruebas);
            if (abs($maximo - $mejor) > self::TOLERANCIA_MEJOR) {
                $advertencias[] = sprintf(
                    'El Mejor de %s (%.2f) no es el máximo de las pruebas (%.2f).',
                    strtoupper(str_replace('_', '/', $clave)),
                    $mejor,
                    $maximo
                );
            }
        }

        // 3. %Pred del informe contra Mejor / Pred.
        foreach (self::PARAMETROS_PCT as $clave) {
            $mejor = $valores[$clave]['mejor'] ?? null;
            $pred = $valores[$clave]['pred'] ?? null;
            $pct = $valores[$clave]['pct'] ?? null;

            if ($mejor === null || $pred === null || $pct === null || $pred <= 0) {
                continue;
            }
            $calculado = $mejor / $pred * 100;
            if (abs($calculado - $pct) > self::TOLERANCIA_PCT) {
                $advertencias[] = sprintf(
                    '%%Pred de %s del informe (%d) no coincide con Mejor / Pred (%.0f).',
                    strtoupper(str_replace('_', '/', $clave)),
                    (int) round($pct),
                    $calculado
                );
            }
        }

        // 4. FET del Mejor debe ser el de la prueba con mayor FEV1.
        if (($resultado['mejor_fet'] ?? null) !== null) {
            $conFev1 = array_filter($maniobras, fn ($m) => $m['fev1_l'] !== null && $m['fet_s'] !== null);
            if ($conFev1 !== []) {
                usort($conFev1, fn ($a, $b) => $b['fev1_l'] <=> $a['fev1_l']);
                if (abs($conFev1[0]['fet_s'] - $resultado['mejor_fet']) > self::TOLERANCIA_FET) {
                    $advertencias[] = sprintf(
                        'FET del Mejor (%.1f) no coincide con la prueba de mayor FEV1 (%.1f).',
                        $resultado['mejor_fet'],
                        $conFev1[0]['fet_s']
                    );
                }
            }
        }

        return $advertencias;
    }

    // ---------------------------------------------------------------
    // Utilidades
    // ---------------------------------------------------------------

    /** "FEV1/FVC [%]" => "fev1_fvc"; "FEF25-75 [L/s]" => "fef25_75"; otra cosa => null. */
    private function claveDeEtiqueta(mixed $celda): ?string
    {
        $texto = $this->normalizarTexto($celda);
        $base = strtoupper(trim(preg_replace('/\s*[\[%].*$/u', '', $texto)));

        return self::ETIQUETAS[$base] ?? null;
    }

    /** Último texto no vacío de la fila que empieza con $etiqueta. */
    private function ultimoTexto(array $filas, string $etiqueta): ?string
    {
        foreach ($filas as $fila) {
            if ($this->normalizarTexto($fila[0] ?? null) !== $etiqueta) {
                continue;
            }
            for ($c = array_key_last($fila); $c > 0; $c--) {
                $texto = $this->normalizarTexto($fila[$c] ?? null);
                if ($texto !== '' && $texto !== 'Previo') {
                    return $texto;
                }
            }
        }

        return null;
    }

    /**
     * Interpreta una celda: número, "72.9*" (con marca), "-" o vacía (sin valor).
     *
     * @return array{valor: ?float, marcado: bool}
     */
    private function celda(array $fila, ?int $columna): array
    {
        if ($columna === null || !array_key_exists($columna, $fila)) {
            return ['valor' => null, 'marcado' => false];
        }

        $bruto = $fila[$columna];

        if (is_int($bruto) || is_float($bruto)) {
            return ['valor' => (float) $bruto, 'marcado' => false];
        }

        $texto = trim((string) $bruto);
        $marcado = str_contains($texto, '*');
        $numero = str_replace('*', '', $texto);

        if ($numero === '' || $numero === '-' || $numero === '—' || !is_numeric($numero)) {
            return ['valor' => null, 'marcado' => $marcado];
        }

        return ['valor' => (float) $numero, 'marcado' => $marcado];
    }

    /** Devuelve la calidad más baja de la lista (según ORDEN_CALIDAD). */
    private function peorCalidad(array $grados): string
    {
        usort($grados, fn ($a, $b) => array_search($b, self::ORDEN_CALIDAD, true) <=> array_search($a, self::ORDEN_CALIDAD, true));

        return $grados[0];
    }

    /** Quita espacios sobrantes y saltos de línea; null se convierte en cadena vacía. */
    private function normalizarTexto(mixed $valor): string
    {
        if ($valor === null) {
            return '';
        }

        return trim(preg_replace('/\s+/u', ' ', (string) $valor));
    }
}