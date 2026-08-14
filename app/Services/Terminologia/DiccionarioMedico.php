<?php

namespace App\Services\Terminologia;

/**
 * Diccionario de equivalencias "lenguaje coloquial del paciente" -> "término médico".
 *
 * Fuente: Manual de Terminología Médica (Prof. Edwin Saldaña Ambulódegui).
 *
 * IMPORTANTE (alcance):
 * Estas equivalencias traducen el SÍNTOMA que el paciente describe a su nombre
 * técnico correcto (ej. "dolor de estómago" -> "Gastralgia"). No son diagnósticos.
 * El diagnóstico probable sigue siendo un paso aparte del prompt, basado en el
 * conjunto de síntomas, y debe seguir marcándose siempre como "probable".
 *
 * Este archivo solo amplía el vocabulario de referencia que ya usaban los
 * prompts; no modifica las fases, reglas ni el formato de salida JSON.
 *
 * NOTA SOBRE DESAMBIGUACIÓN:
 * Cuando dos variantes clínicas distintas (no sinónimos) compartían antes una
 * sola entrada coloquial (ej. "mareo" -> "Vértigo (si es rotatorio) / Mareo
 * inespecífico"), se separaron en dos entradas coloquiales distintas, cada
 * una con su criterio de elección explícito en la clave. Así la IA no tiene
 * que adivinar cuál de las dos aplica ni queda la posibilidad de que copie
 * la barra "/" literal en su respuesta.
 * Cuando ambos términos sí son sinónimos intercambiables (ej. "Oftalmalgia /
 * Oftalmodinia"), se mantienen juntos con "/", ya que no hay ambigüedad
 * clínica real que resolver.
 */
class DiccionarioMedico
{
    /**
     * Mapa coloquial => término médico, agrupado por sistema/aparato.
     */
    public const TERMINOS = [
        'Síntomas digestivos' => [
            'dolor de estómago (difuso, en abdomen superior)' => 'Gastralgia',
            'dolor de estómago (localizado en epigastrio)' => 'Epigastralgia',
            'ardor de estómago' => 'Pirosis',
            'vómito' => 'Emesis',
            'vómito con sangre' => 'Hematemesis',
            'diarrea con sangre o heces oscuras' => 'Melena',
            'estreñimiento' => 'Constipación',
            'distensión abdominal por gases' => 'Meteorismo',
            'expulsión de gases' => 'Flatulencia',
            'dolor de barriga tipo cólico (niños)' => 'Cólico abdominal',
            'dificultad para tragar' => 'Disfagia',
            'dolor al tragar' => 'Odinofagia',
            'hígado inflamado o agrandado' => 'Hepatomegalia',
            'vesícula inflamada' => 'Colecistitis',
            'piedras en la vesícula' => 'Colelitiasis',
            'boca seca' => 'Xerostomía',
            'pérdida de apetito' => 'Anorexia',
            'flujo de saliva excesivo' => 'Sialorrea',
        ],

        'Síntomas urinarios y genitourinarios' => [
            'sangre en la orina' => 'Hematuria',
            'dolor o ardor al orinar' => 'Disuria',
            'orinar con mucha frecuencia o en exceso' => 'Poliuria',
            'no poder orinar en absoluto' => 'Anuria',
            'orinar en cantidad muy reducida' => 'Oliguria',
            'sed excesiva' => 'Polidipsia',
            'flujo vaginal' => 'Leucorrea',
            'sangrado uterino fuera de la regla' => 'Metrorragia',
            'menstruación dolorosa' => 'Dismenorrea',
            'ausencia de menstruación' => 'Amenorrea',
            'regla muy abundante o prolongada' => 'Menorragia',
        ],

        'Síntomas respiratorios' => [
            'falta de aire / dificultad para respirar' => 'Disnea',
            'respiración muy rápida' => 'Taquipnea',
            'respiración muy lenta' => 'Bradipnea',
            'tos con flema' => 'Tos productiva',
            'flema con sangre' => 'Hemoptisis',
            'dolor al respirar (punzada en el costado)' => 'Dolor pleurítico',
            'pérdida completa de la voz' => 'Afonía',
            'ronquera o voz débil' => 'Disfonía',
            'gripa / catarro' => 'Infección respiratoria aguda (viral)',
        ],

        'Síntomas cardiovasculares' => [
            'corazón acelerado' => 'Taquicardia',
            'corazón muy lento' => 'Bradicardia',
            'dolor de pecho' => 'Dolor torácico / Precordialgia',
            'hinchazón de piernas' => 'Edema de miembros inferiores',
            'presión alta' => 'Hipertensión arterial sistémica',
            'presión baja' => 'Hipotensión arterial',
            'venas várices' => 'Flebectasia (várices)',
        ],

        'Síntomas neurológicos' => [
            'dolor de cabeza' => 'Cefalea / Cefalalgia',
            'mareo con sensación de giro o rotación' => 'Vértigo',
            'mareo sin sensación de giro' => 'Mareo inespecífico',
            'desmayo' => 'Síncope',
            'hormigueo' => 'Parestesia',
            'sensibilidad disminuida en la piel' => 'Hipoestesia',
            'sensibilidad abolida por completo en la piel' => 'Anestesia',
            'pérdida de memoria' => 'Amnesia',
            'dificultad para pronunciar o articular palabras (sin problema de lenguaje)' => 'Disartria',
            'dificultad para encontrar o comprender palabras (problema de lenguaje)' => 'Afasia',
            'pérdida parcial de fuerza en un lado del cuerpo' => 'Hemiparesia',
            'pérdida total de fuerza / parálisis en un lado del cuerpo' => 'Hemiplejía',
        ],

        'Síntomas musculoesqueléticos' => [
            'dolor articular' => 'Artralgia',
            'dolor muscular' => 'Mialgia',
            'dolor de huesos' => 'Ostealgia / Osteodinia',
            'dolor de espalda baja' => 'Lumbalgia',
            'dolor de cadera' => 'Coxalgia',
            'articulación inflamada' => 'Artritis',
            'rigidez o bloqueo permanente de una articulación' => 'Anquilosis',
        ],

        'Síntomas dermatológicos' => [
            'piel amarilla' => 'Ictericia',
            'comezón' => 'Prurito',
            'ronchas' => 'Urticaria',
            'piel muy reseca' => 'Xerodermia',
            'moretón' => 'Equimosis',
            'sangrado bajo la piel' => 'Hematoma',
        ],

        'Síntomas de oído, ojos y garganta' => [
            'dolor de oído' => 'Otalgia',
            'supuración de oído' => 'Otorrea',
            'zumbido de oído' => 'Acúfeno / Tinnitus',
            'pérdida de audición' => 'Hipoacusia',
            'dolor de ojos' => 'Oftalmalgia / Oftalmodinia',
            'lagrimeo excesivo' => 'Epífora',
            'molestia o sensibilidad a la luz' => 'Fotofobia',
            'dolor de garganta' => 'Odinofagia / Faringodinia',
        ],

        'Síntomas generales / constitucionales' => [
            'fiebre' => 'Pirexia / Hipertermia',
            'pérdida de peso moderada' => 'Adelgazamiento',
            'pérdida de peso extrema con desgaste muscular' => 'Caquexia',
            'cansancio extremo' => 'Astenia',
            'debilidad general' => 'Adinamia',
            'sudoración excesiva' => 'Hiperhidrosis',
            'hinchazón generalizada del cuerpo' => 'Edema generalizado',
            'sangrado (en general)' => 'Hemorragia',
        ],
    ];

    /**
     * Devuelve el diccionario completo aplanado (coloquial => término médico),
     * sin agrupar por categoría. Útil si se necesita en formato de array simple.
     */
    public static function aplanado(): array
    {
        $resultado = [];
        foreach (self::TERMINOS as $categoria => $terminos) {
            foreach ($terminos as $coloquial => $medico) {
                $resultado[$coloquial] = $medico;
            }
        }
        return $resultado;
    }

    /**
     * Genera el bloque de texto (en el mismo formato "En lugar de 'X' -> 'Y'"
     * que ya usaban los prompts) listo para interpolar dentro de un prompt de IA.
     *
     * @param array|null $categorias Si se pasa, limita el texto a esas categorías
     *                                (por ejemplo, para prompts más cortos/enfocados).
     */
    public static function textoReferencia(?array $categorias = null): string
    {
        $texto = "Nota: cuando la clave coloquial ya distingue una condición o característica "
               . "específica (ej. 'mareo con sensación de giro' vs 'mareo sin sensación de giro'), "
               . "usa el término médico de la entrada que coincida con lo que describió el paciente, "
               . "no la otra. Cuando el término médico tenga dos opciones unidas por '/', son "
               . "sinónimos intercambiables: usa solo uno de los dos, nunca copies la barra '/' "
               . "literal en tu respuesta.\n";

        foreach (self::TERMINOS as $categoria => $terminos) {
            if ($categorias !== null && !in_array($categoria, $categorias, true)) {
                continue;
            }

            $texto .= "\n{$categoria}:\n";
            foreach ($terminos as $coloquial => $medico) {
                $texto .= "- En lugar de '{$coloquial}' -> '{$medico}'\n";
            }
        }

        return $texto;
    }
}