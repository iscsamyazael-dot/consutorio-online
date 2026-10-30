<?php

namespace App\Services\ClinicaTrabajo;

use App\Models\ClinicaTrabajo\ValoracionEspirometria;

/**
 * Genera las gráficas del reporte de espirometría como imágenes PNG en base64.
 *
 * Se dibujan con GD porque dompdf no ejecuta JavaScript (no sirve Chart.js).
 * Cada llamada usa los valores de la valoración que recibe, así que las gráficas
 * son distintas para cada paciente.
 *
 * - Flujo-volumen y volumen-tiempo: aproximadas, igual que en pantalla, porque se
 *   generan a partir de la tabla de parámetros y no de los datos crudos del equipo.
 * - Puntuación Z: usa los valores que trae el informe.
 *
 * Cada método devuelve null si faltan los datos necesarios, para que el reporte
 * simplemente no muestre esa gráfica.
 */
class GraficasEspirometria
{
    private const ANCHO = 420;
    private const ALTO_CURVAS = 280;

    private const COLOR_MEJOR = [30, 64, 175];
    private const COLOR_PREDICHO = [140, 140, 140];
    private const COLOR_PUNTO = [30, 64, 175];
    private const COLOR_REJILLA = [226, 232, 240];
    private const COLOR_EJE = [55, 65, 81];
    private const COLOR_ZONA_BAJA = [253, 230, 190];
    private const COLOR_ZONA_NORMAL = [220, 242, 220];
    private const COLOR_BLANCO = [255, 255, 255];

    /**
     * Curva flujo-volumen: espiración de 0 a FVC e inspiración de FIVC a 0.
     * Si el informe trae el predicho, se dibuja punteada en gris.
     */
    public function flujoVolumen(ValoracionEspirometria $v): ?string
    {
        $fvc = $this->num($v->fvc_l);
        $pef = $this->num($v->pef_l_s);
        if (!$this->disponible() || $fvc === null || $pef === null || $fvc <= 0) {
            return null;
        }

        $fivc = $this->num($v->fivc_l) ?? 0.0;
        $pif = $this->num($v->pif_l_s) ?? 0.0;

        $fvcP = $this->num($v->fvc_pred);
        $pefP = $this->num($v->pef_pred);
        $fivcP = $this->num($v->fivc_pred) ?? 0.0;
        $pifP = $this->num($v->pif_pred) ?? 0.0;

        $ymax = max(12, ceil(max($pef, $pefP ?? 0.0)) + 1);
        $xmax = max(8, ceil(max($fvc, $fvcP ?? 0.0)) + 1);

        $e = $this->ejes(self::ANCHO, self::ALTO_CURVAS, -1, $xmax, -$ymax, $ymax);
        $img = $this->lienzo(self::ANCHO, self::ALTO_CURVAS);
        $this->dibujarEjes($img, $e, 1, 2, 'Volumen [L]', 'Flujo [L/s]');

        if ($fvcP !== null && $pefP !== null && $fvcP > 0) {
            $this->linea($img, $e, $this->puntosFlujoVolumen($fvcP, $pefP, $fivcP, $pifP), self::COLOR_PREDICHO, 1, true);
        }

        $this->linea($img, $e, $this->puntosFlujoVolumen($fvc, $pef, $fivc, $pif), self::COLOR_MEJOR, 2);

        return $this->png($img);
    }

    /**
     * Curva volumen-tiempo de 0 a 14 s, con el FEV1 a 1 s marcado.
     * Si el informe trae el predicho, se dibuja punteada en gris.
     */
    public function volumenTiempo(ValoracionEspirometria $v): ?string
    {
        $fvc = $this->num($v->fvc_l);
        $fev1 = $this->num($v->fev1_l);
        if (!$this->disponible() || $fvc === null || $fev1 === null || $fvc <= 0) {
            return null;
        }

        $fvcP = $this->num($v->fvc_pred);
        $fev1P = $this->num($v->fev1_pred);

        $ymax = max(5, ceil(max($fvc, $fvcP ?? 0.0)) + 1);

        $e = $this->ejes(self::ANCHO, self::ALTO_CURVAS, 0, 14, 0, $ymax);
        $img = $this->lienzo(self::ANCHO, self::ALTO_CURVAS);
        $this->dibujarEjes($img, $e, 2, 1, 'Tiempo [s]', 'Volumen [L]');

        if ($fvcP !== null && $fev1P !== null && $fvcP > 0) {
            $this->linea($img, $e, $this->puntosVolumenTiempo($fvcP, $fev1P), self::COLOR_PREDICHO, 1, true);
            $this->marcador($img, $e, 1.0, $this->volumenEnTiempo(1.0, $fvcP, $fev1P), self::COLOR_PREDICHO);
        }

        $this->linea($img, $e, $this->puntosVolumenTiempo($fvc, $fev1), self::COLOR_MEJOR, 2);
        $this->marcador($img, $e, 1.0, $this->volumenEnTiempo(1.0, $fvc, $fev1), self::COLOR_PUNTO);

        return $this->png($img);
    }

    /**
     * Puntuación Z por parámetro. Solo dibuja los parámetros que traen Z.
     * Zona naranja: por debajo del LLN (-1.645). Línea punteada: predicho (0).
     */
    public function puntuacionZ(ValoracionEspirometria $v): ?string
    {
        $parametros = [
            'FVC' => $v->fvc_z,
            'FEV1' => $v->fev1_z,
            'FEV1/FVC' => $v->fev1_fvc_z,
            'FEF25-75' => $v->fef25_75_z,
            'PEF' => $v->pef_z,
            'FIVC' => $v->fivc_z,
        ];

        $filas = [];
        foreach ($parametros as $etiqueta => $valor) {
            $z = $this->num($valor);
            if ($z !== null) {
                $filas[$etiqueta] = $z;
            }
        }

        if (!$this->disponible() || $filas === []) {
            return null;
        }

        $ancho = self::ANCHO;
        $alto = 60 + count($filas) * 30 + 50;
        $x0 = 100;
        $x1 = $ancho - 25;
        $y1 = 20;
        $y0 = $alto - 40;
        $zmin = -5;
        $zmax = 3;

        $mz = fn (float $z): int => (int) round($x0 + ($z - $zmin) / ($zmax - $zmin) * ($x1 - $x0));

        $img = $this->lienzo($ancho, $alto);
        $negro = $this->color($img, self::COLOR_EJE);
        $rejilla = $this->color($img, self::COLOR_REJILLA);
        $gris = $this->color($img, self::COLOR_PREDICHO);
        $blanco = $this->color($img, self::COLOR_BLANCO);

        // Zonas: por debajo del LLN en naranja, el resto en verde
        $xLln = $mz(-1.645);
        imagefilledrectangle($img, $x0, $y1, $xLln, $y0, $this->color($img, self::COLOR_ZONA_BAJA));
        imagefilledrectangle($img, $xLln, $y1, $x1, $y0, $this->color($img, self::COLOR_ZONA_NORMAL));

        // Rejilla y etiquetas del eje Z
        for ($z = $zmin; $z <= $zmax; $z++) {
            $px = $mz($z);
            imageline($img, $px, $y1, $px, $y0, $rejilla);
            $this->texto($img, $px - 4, $y0 + 6, (string) $z, $negro);
        }
        $this->texto($img, (int) (($x0 + $x1) / 2) - 30, $y0 + 20, 'puntuación Z', $negro);

        // Líneas de referencia: LLN (-1.645) y límite superior (+1.645), en negro
        imagesetthickness($img, 2);
        imageline($img, $xLln, $y1, $xLln, $y0, $negro);
        imageline($img, $mz(1.645), $y1, $mz(1.645), $y0, $negro);
        imagesetthickness($img, 1);

        // Predicho (0), punteada en gris
        imagesetstyle($img, array_merge(array_fill(0, 4, $gris), array_fill(0, 3, $blanco)));
        imageline($img, $mz(0), $y1, $mz(0), $y0, IMG_COLOR_STYLED);

        $this->texto($img, $xLln - 12, $y1 - 12, 'LLN', $negro);
        $this->texto($img, $mz(0) - 16, $y1 - 12, 'predicho', $negro);

        // Un punto por parámetro
        $fila = 0;
        foreach ($filas as $etiqueta => $z) {
            $cy = $y1 + 15 + $fila * 30;
            $px = $mz($z);
            $this->texto($img, 8, $cy - 6, $etiqueta, $negro);
            imagefilledellipse($img, $px, $cy, 9, 9, $this->color($img, self::COLOR_PUNTO));
            $this->texto($img, $px + 8, $cy - 6, $this->fmt($z), $negro);
            $fila++;
        }

        return $this->png($img);
    }

    // ---------------------------------------------------------------
    // Curvas (misma matemática que ParametrosFvlEspirometria.vue)
    // ---------------------------------------------------------------

    /** Forma de la espiración: u = fracción exhalada desde TLC (0) hasta FVC (1). */
    private function formaEspiracion(float $u): float
    {
        return sqrt(1 - exp(-$u / 0.05)) * pow(1 - $u, 1.2);
    }

    /** Máximo de la forma, para que el pico de la curva sea exactamente el PEF. */
    private function maximoForma(): float
    {
        $maximo = 0.0;
        for ($i = 0; $i <= 1000; $i++) {
            $maximo = max($maximo, $this->formaEspiracion($i / 1000));
        }
        return $maximo;
    }

    /** Espiración de 0 a FVC y luego inspiración de FIVC a 0. */
    private function puntosFlujoVolumen(float $fvc, float $pef, float $fivc, float $pif): array
    {
        $pts = [];
        $maximo = $this->maximoForma();

        for ($i = 0; $i <= 100; $i++) {
            $u = $i / 100;
            $pts[] = ['x' => $u * $fvc, 'y' => $pef * $this->formaEspiracion($u) / $maximo];
        }

        if ($fivc > 0 && $pif > 0) {
            for ($i = 0; $i <= 100; $i++) {
                $u = $i / 100;
                $pts[] = ['x' => $fvc - $fivc + $u * $fivc, 'y' => -$pif * pow(sin(M_PI * $u), 0.3)];
            }
        }

        return $pts;
    }

    /** V(t) = FVC · (1 − e^(−t/τ)), con τ tal que el FEV1 se alcanza en t = 1 s. */
    private function volumenEnTiempo(float $t, float $fvc, float $fev1): float
    {
        $ratio = min(max($fev1 / $fvc, 0.05), 0.99);
        $tau = -1 / log(1 - $ratio);

        return $fvc * (1 - exp(-$t / $tau));
    }

    private function puntosVolumenTiempo(float $fvc, float $fev1): array
    {
        $pts = [];
        for ($i = 0; $i <= 140; $i++) {
            $t = $i / 10;
            $pts[] = ['x' => $t, 'y' => $this->volumenEnTiempo($t, $fvc, $fev1)];
        }
        return $pts;
    }

    // ---------------------------------------------------------------
    // Dibujo con GD
    // ---------------------------------------------------------------

    /** Área de graficación: deja margen para las etiquetas de los ejes. */
    private function ejes(int $ancho, int $alto, float $xmin, float $xmax, float $ymin, float $ymax): array
    {
        return [
            'x0' => 55,
            'x1' => $ancho - 15,
            'y1' => 20,
            'y0' => $alto - 40,
            'xmin' => $xmin,
            'xmax' => $xmax,
            'ymin' => $ymin,
            'ymax' => $ymax,
        ];
    }

    /** Convierte un valor X de la gráfica a píxeles. */
    private function mx(array $e, float $x): int
    {
        return (int) round($e['x0'] + ($x - $e['xmin']) / ($e['xmax'] - $e['xmin']) * ($e['x1'] - $e['x0']));
    }

    /** Convierte un valor Y de la gráfica a píxeles (el eje crece hacia arriba). */
    private function my(array $e, float $y): int
    {
        return (int) round($e['y0'] - ($y - $e['ymin']) / ($e['ymax'] - $e['ymin']) * ($e['y0'] - $e['y1']));
    }

    private function dibujarEjes($img, array $e, float $pasoX, float $pasoY, string $etiquetaX, string $etiquetaY): void
    {
        $rejilla = $this->color($img, self::COLOR_REJILLA);
        $negro = $this->color($img, self::COLOR_EJE);

        for ($x = $e['xmin']; $x <= $e['xmax'] + 1e-9; $x += $pasoX) {
            $px = $this->mx($e, $x);
            imageline($img, $px, $e['y1'], $px, $e['y0'], $rejilla);
            $this->texto($img, $px - 6, $e['y0'] + 4, $this->fmt($x), $negro);
        }

        for ($y = $e['ymin']; $y <= $e['ymax'] + 1e-9; $y += $pasoY) {
            $py = $this->my($e, $y);
            imageline($img, $e['x0'], $py, $e['x1'], $py, $rejilla);
            $this->texto($img, $e['x0'] - 34, $py - 6, $this->fmt($y), $negro);
        }

        // Línea de base (cero) y ejes
        $cero = $this->my($e, 0);
        imageline($img, $e['x0'], $cero, $e['x1'], $cero, $negro);
        imageline($img, $e['x0'], $e['y0'], $e['x1'], $e['y0'], $negro);
        imageline($img, $e['x0'], $e['y1'], $e['x0'], $e['y0'], $negro);

        $this->texto($img, $e['x1'] - 90, $e['y0'] + 20, $etiquetaX, $negro);
        $this->texto($img, $e['x0'] + 4, $e['y1'] - 12, $etiquetaY, $negro);
    }

    /** Dibuja una serie de puntos. Con $punteada, la línea es de rayas en vez de continua. */
    private function linea($img, array $e, array $pts, array $rgb, int $grosor = 2, bool $punteada = false): void
    {
        $color = $this->color($img, $rgb);

        if ($punteada) {
            $blanco = $this->color($img, self::COLOR_BLANCO);
            imagesetstyle($img, array_merge(array_fill(0, 4, $color), array_fill(0, 3, $blanco)));
            $color = IMG_COLOR_STYLED;
        }

        imagesetthickness($img, $grosor);
        for ($i = 1; $i < count($pts); $i++) {
            imageline(
                $img,
                $this->mx($e, $pts[$i - 1]['x']), $this->my($e, $pts[$i - 1]['y']),
                $this->mx($e, $pts[$i]['x']), $this->my($e, $pts[$i]['y']),
                $color
            );
        }
        imagesetthickness($img, 1);
    }

    private function marcador($img, array $e, float $x, float $y, array $rgb): void
    {
        imagefilledellipse($img, $this->mx($e, $x), $this->my($e, $y), 8, 8, $this->color($img, $rgb));
    }

    private function lienzo(int $ancho, int $alto)
    {
        $img = imagecreatetruecolor($ancho, $alto);
        imagefilledrectangle($img, 0, 0, $ancho, $alto, $this->color($img, self::COLOR_BLANCO));
        return $img;
    }

    private function color($img, array $rgb): int
    {
        return imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);
    }

    /** Texto con las fuentes integradas de GD (ISO-8859-1, por eso se convierte desde UTF-8). */
    private function texto($img, int $x, int $y, string $texto, int $color, int $fuente = 3): void
    {
        imagestring($img, $fuente, $x, $y, mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8'), $color);
    }

    /** Escribe la imagen en memoria y la devuelve lista para <img src="...">. */
    private function png($img): string
    {
        ob_start();
        imagepng($img);
        $binario = ob_get_clean();
        imagedestroy($img);

        return 'data:image/png;base64,' . base64_encode($binario);
    }

    private function num(mixed $valor): ?float
    {
        return ($valor === null || $valor === '') ? null : (float) $valor;
    }

    private function fmt(float $valor): string
    {
        return rtrim(rtrim(number_format($valor, 1, '.', ''), '0'), '.');
    }

    private function disponible(): bool
    {
        return function_exists('imagecreatetruecolor') && function_exists('imagepng');
    }
}