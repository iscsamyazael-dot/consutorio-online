<?php

namespace App\Services\ClinicaTrabajo;

/**
 * Genera el audiograma del reporte de audiometría como imagen PNG en base64.
 *
 * Se dibuja con GD porque dompdf no ejecuta JavaScript. Igual que GraficasEspirometria,
 * devuelve null si no hay datos, para que el reporte simplemente no muestre la gráfica.
 *
 * Entrada (la normaliza el controlador):
 * [
 *   'derecho'   => ['aerea' => [125 => 25, ...], 'osea' => [250 => null, ...], 'enmascarado' => [250 => null, ...]],
 *   'izquierdo' => ['aerea' => [...], 'osea' => [...], 'enmascarado' => [...]],
 * ]
 * Valores: umbrales en dB HL. null o ausente = no evaluado.
 *
 * Convención audiométrica:
 *   - Eje X: frecuencias en Hz, equiespaciadas.
 *   - Eje Y: dB HL, con 0 arriba y valores altos hacia abajo.
 *   - Derecho: O en vía aérea, < en vía ósea y triángulo en enmascarado, en rojo.
 *   - Izquierdo: X en vía aérea, > en vía ósea y cuadro en enmascarado, en azul.
 */
class GraficasAudiologia
{
    private const ANCHO = 600;
    private const ALTO = 360;

    // Área de graficación (deja margen para etiquetas)
    private const X0 = 55;
    private const X1 = 585;
    private const Y0 = 20;
    private const Y1 = 290;

    private const FRECUENCIAS = [125, 250, 500, 1000, 2000, 3000, 4000, 6000, 8000];
    private const DB_MIN = -10;
    private const DB_MAX = 120;

    private const COLOR_DERECHO = [185, 28, 28];
    private const COLOR_IZQUIERDO = [30, 64, 175];
    private const COLOR_REJILLA = [226, 232, 240];
    private const COLOR_EJE = [55, 65, 81];
    private const COLOR_BLANCO = [255, 255, 255];

    /**
     * Audiograma con ambos oídos: vía aérea (continua), vía ósea (punteada) y enmascarado (símbolos).
     */
    public function audiograma(array $umbrales): ?string
    {
        if (!$this->disponible() || !$this->hayDatos($umbrales)) {
            return null;
        }

        $img = imagecreatetruecolor(self::ANCHO, self::ALTO);
        imagefilledrectangle($img, 0, 0, self::ANCHO, self::ALTO, $this->color($img, self::COLOR_BLANCO));

        $this->dibujarEjes($img);

        $oidos = [
            'derecho' => [
                'color' => self::COLOR_DERECHO,
                'marcaAerea' => 'circulo',
                'marcaOsea' => 'menor',
                'marcaEnmascarado' => 'triangulo',
            ],
            'izquierdo' => [
                'color' => self::COLOR_IZQUIERDO,
                'marcaAerea' => 'equis',
                'marcaOsea' => 'mayor',
                'marcaEnmascarado' => 'cuadro',
            ],
        ];

        foreach ($oidos as $clave => $config) {
            $color = $this->color($img, $config['color']);

            $aerea = $this->puntos($umbrales[$clave]['aerea'] ?? []);
            $osea = $this->puntos($umbrales[$clave]['osea'] ?? []);
            $enmascarado = $this->puntos($umbrales[$clave]['enmascarado'] ?? []);

            $this->linea($img, $aerea, $color, false);
            $this->linea($img, $osea, $color, true);

            $this->marcadores($img, $aerea, $color, $config['marcaAerea']);
            $this->marcadores($img, $osea, $color, $config['marcaOsea']);
            $this->marcadores($img, $enmascarado, $color, $config['marcaEnmascarado']);
        }

        return $this->png($img);
    }

    // ---------------------------------------------------------------
    // Ejes y leyenda
    // ---------------------------------------------------------------

    private function dibujarEjes($img): void
    {
        $negro = $this->color($img, self::COLOR_EJE);
        $rejilla = $this->color($img, self::COLOR_REJILLA);

        // Líneas horizontales de dB HL cada 10
        for ($db = 0; $db <= self::DB_MAX; $db += 10) {
            $y = $this->py((float) $db);
            imageline($img, self::X0, $y, self::X1, $y, $rejilla);
            $this->texto($img, self::X0 - 30, $y - 6, (string) $db, $negro);
        }

        // Líneas verticales por frecuencia
        foreach (self::FRECUENCIAS as $i => $frecuencia) {
            $x = $this->px($i);
            imageline($img, $x, self::Y0, $x, self::Y1, $rejilla);
            $this->texto($img, $x - 12, self::Y1 + 6, (string) $frecuencia, $negro);
        }

        // Marco del área
        imagerectangle($img, self::X0, self::Y0, self::X1, self::Y1, $negro);

        $this->texto($img, self::X0, 4, 'Umbral (dB HL)', $negro);
        $this->texto($img, self::X1 - 80, self::Y1 + 22, 'Frecuencia (Hz)', $negro);
        $this->leyenda($img);

    }

    // ---------------------------------------------------------------
    // Series y marcadores
    // ---------------------------------------------------------------

    /**
     * Convierte { frecuencia => umbral } en puntos [x, y] en píxeles,
     * en el orden de FRECUENCIAS y solo para frecuencias con valor.
     */
    private function puntos(array $umbrales): array
    {
        $pts = [];
        foreach (self::FRECUENCIAS as $i => $frecuencia) {
            $valor = $umbrales[$frecuencia] ?? null;
            if ($valor === null || $valor === '') {
                continue;
            }
            $pts[] = [$this->px($i), $this->py((float) $valor)];
        }
        return $pts;
    }

    /** Une puntos consecutivos. Con $punteada, la línea es de rayas (vía ósea). */
    private function linea($img, array $pts, int $color, bool $punteada): void
    {
        if (count($pts) < 2) {
            return;
        }

        if ($punteada) {
            $blanco = $this->color($img, self::COLOR_BLANCO);
            imagesetstyle($img, array_merge(array_fill(0, 4, $color), array_fill(0, 3, $blanco)));
            $color = IMG_COLOR_STYLED;
        }

        imagesetthickness($img, 2);
        for ($i = 1; $i < count($pts); $i++) {
            imageline($img, $pts[$i - 1][0], $pts[$i - 1][1], $pts[$i][0], $pts[$i][1], $color);
        }
        imagesetthickness($img, 1);
    }

    /** Dibuja el símbolo de cada punto con trazo de 2 px para que se vea bien en el PDF. */
    private function marcadores($img, array $pts, int $color, string $tipo): void
    {
        imagesetthickness($img, 2);

        foreach ($pts as [$x, $y]) {
            match ($tipo) {
                'circulo' => imageellipse($img, $x, $y, 14, 14, $color),
                'equis' => $this->equis($img, $x, $y, $color),
                'menor' => $this->flecha($img, $x, $y, $color, -1),
                'mayor' => $this->flecha($img, $x, $y, $color, 1),
                'triangulo' => $this->triangulo($img, $x, $y, $color),
                'cuadro' => imagerectangle($img, $x - 6, $y - 6, $x + 6, $y + 6, $color),
            };
        }

        imagesetthickness($img, 1);
    }

    private function equis($img, int $x, int $y, int $color): void
    {
        imageline($img, $x - 6, $y - 6, $x + 6, $y + 6, $color);
        imageline($img, $x + 6, $y - 6, $x - 6, $y + 6, $color);
    }

    /** Dirección -1 = "<" (ósea derecha), 1 = ">" (ósea izquierda). */
    private function flecha($img, int $x, int $y, int $color, int $direccion): void
    {
        $punta = $x + (5 * $direccion);
        $cola = $x - (6 * $direccion);
        imageline($img, $cola, $y - 7, $punta, $y, $color);
        imageline($img, $punta, $y, $cola, $y + 7, $color);
    }

    private function triangulo($img, int $x, int $y, int $color): void
    {
        imageline($img, $x, $y - 8, $x - 7, $y + 6, $color);
        imageline($img, $x - 7, $y + 6, $x + 7, $y + 6, $color);
        imageline($img, $x + 7, $y + 6, $x, $y - 8, $color);
    }

    // ---------------------------------------------------------------
    // Conversión y utilidades
    // ---------------------------------------------------------------

    /** Posición X de la frecuencia con índice $i. */
    private function px(int $i): int
    {
        $total = count(self::FRECUENCIAS) - 1;
        return (int) round(self::X0 + $i * (self::X1 - self::X0) / $total);
    }

    /** Posición Y de un umbral en dB HL (0 arriba, valores altos abajo). */
    private function py(float $db): int
    {
        return (int) round(self::Y0 + ($db - self::DB_MIN) / (self::DB_MAX - self::DB_MIN) * (self::Y1 - self::Y0));
    }

    private function hayDatos(array $umbrales): bool
    {
        foreach (['derecho', 'izquierdo'] as $oido) {
            foreach (['aerea', 'osea', 'enmascarado'] as $via) {
                foreach ($umbrales[$oido][$via] ?? [] as $valor) {
                    if ($valor !== null && $valor !== '') {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    private function color($img, array $rgb): int
    {
        return imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);
    }

    /** Texto con las fuentes integradas de GD (ISO-8859-1, por eso se convierte desde UTF-8). */
    private function texto($img, int $x, int $y, string $texto, int $color, int $fuente = 2): void
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

    private function disponible(): bool
    {
        return function_exists('imagecreatetruecolor') && function_exists('imagepng');
    }

        /**
     * Leyenda debajo del eje: cada símbolo se dibuja con la misma función
     * que usa la gráfica, así que coincide exactamente con los puntos.
     */
    private function leyenda($img): void
    {
        $negro = $this->color($img, self::COLOR_EJE);

        $filas = [
            [
                'nombre' => 'Der.',
                'color' => self::COLOR_DERECHO,
                'y' => self::Y1 + 42,
                'simbolos' => [
                    ['circulo', 'aérea'],
                    ['menor', 'ósea'],
                    ['triangulo', 'enmasc.'],
                ],
            ],
            [
                'nombre' => 'Izq.',
                'color' => self::COLOR_IZQUIERDO,
                'y' => self::Y1 + 58,
                'simbolos' => [
                    ['equis', 'aérea'],
                    ['mayor', 'ósea'],
                    ['cuadro', 'enmasc.'],
                ],
            ],
        ];

        foreach ($filas as $fila) {
            $color = $this->color($img, $fila['color']);
            $x = self::X0;

            $this->texto($img, $x, $fila['y'] - 6, $fila['nombre'], $negro);
            $x += 36;

            foreach ($fila['simbolos'] as [$tipo, $etiqueta]) {
                $this->marcadores($img, [[$x, $fila['y']]], $color, $tipo);
                $this->texto($img, $x + 8, $fila['y'] - 6, $etiqueta, $negro);
                $x += 115;
            }
        }
    }
}