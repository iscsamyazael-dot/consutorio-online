<?php

namespace App\Imports\ClinicaTrabajo;

use App\Models\ClinicaTrabajo\PacientesClinicaTrabajo;
use App\Models\ClinicaTrabajo\EmpresaCliente;
use App\Models\ClinicaTrabajo\PuestoTrabajo;
use App\Models\ClinicaTrabajo\DatosLaboral;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PacientesImport
{   
    private const CURP_REGEX = '/^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[A-Z0-9][0-9]$/';
    private const GENEROS = ['Mujer', 'Hombre', 'No binario', 'Otro', 'Prefiero no decir'];
    public function process(string $filePath): array
    {
        $imported = 0;
        $errors = 0;
        $advertencias = [];

        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (empty($rows)) {
                throw new \Exception('El archivo está vacío.');
            }

            $headers = array_shift($rows);
            $headerMap = [];
            foreach ($headers as $index => $header) {
                $headerMap[strtolower(trim($header))] = $index;
            }

            // ✅ FUNCIÓN SEGURA: Evita el error "Undefined array key"
            $get = function (array $row, string $key) use ($headerMap) {
                if (!isset($headerMap[$key])) return null;
                $val = $row[$headerMap[$key]] ?? null;
                return $val === null ? null : trim((string)$val);
            };

            // ✅ SANITIZADORES: Previenen errores de base de datos (N/A y espacios en RH)
            $limpiarCedula = function (?string $valor): ?string {
                if (!$valor) return null;
                $valor = strtoupper(trim($valor));
                return in_array($valor, ['N/A', 'NA', 'NO APLICA', '']) ? null : $valor;
            };

            $limpiarRh = function (?string $valor): ?string {
                if (!$valor) return null;
                $valor = trim($valor);
                return in_array($valor, ['+', '-']) ? $valor : null;
            };

            $limpiarTipoSangre = function (?string $valor): ?string {
                if (!$valor) return null;
                $valor = strtoupper(trim($valor));
                return in_array($valor, ['A', 'B', 'AB', 'O']) ? $valor : null;
            };


            $curpsEnArchivo = [];

            $avisar = function (int $fila, string $campo, string $mensaje) use (&$advertencias) {
                $advertencias[] = ['fila' => $fila, 'campo' => $campo, 'mensaje' => $mensaje];
            };

            foreach ($rows as $rowIndex => $row) {
                if (empty(array_filter($row))) continue;

                $nombreCompleto = $get($row, 'nombre_completo');
                if (empty($nombreCompleto)) continue;

                $fila = $rowIndex + 2;

                // CURP: formato oficial. Bloquea la fila.
                $curp = strtoupper(trim($get($row, 'curp') ?? ''));
                $curp = $curp === '' ? null : $curp;

                if ($curp !== null && !preg_match(self::CURP_REGEX, $curp)) {
                    $errors++;
                    $avisar($fila, 'curp',
                        "CURP '" . $curp . "' inválida: tiene " . strlen($curp) .
                        " caracteres, debe tener 18 y seguir el formato oficial. Fila omitida.");
                    Log::warning("CURP inválida en fila {$fila}: '{$curp}'");
                    continue;
                }

                // CURP repetida en el archivo o en la base. Bloquea la fila.
                if ($curp !== null && (isset($curpsEnArchivo[$curp])
                    || PacientesClinicaTrabajo::withTrashed()->where('curp', $curp)->exists())) {
                    $errors++;
                    $avisar($fila, 'curp', "La CURP '" . $curp . "' ya está registrada. Fila omitida.");
                    continue;
                }
                if ($curp !== null) {
                    $curpsEnArchivo[$curp] = $fila;
                }

                // Género: debe existir en el enum de la base. Bloquea la fila.
                $genero = $get($row, 'genero');
                if ($genero !== null && $genero !== '' &&
                    !in_array(mb_strtolower($genero), array_map('mb_strtolower', self::GENEROS), true)) {
                    $errors++;
                    $avisar($fila, 'genero',
                        "Género '" . $genero . "' no válido. Valores permitidos: " .
                        implode(', ', self::GENEROS) . ". Fila omitida.");
                    continue;
                }

                // Avisos que no detienen la fila: el dato se guarda vacío.
                $fechaRaw = $get($row, 'fecha_nacimiento');
                if ($fechaRaw && $this->parseDate($fechaRaw) === null) {
                    $avisar($fila, 'fecha_nacimiento', "Fecha '" . $fechaRaw . "' no reconocida. Se guardará vacía.");
                }

                $rhRaw = $get($row, 'rh');
                if ($rhRaw && $limpiarRh($rhRaw) === null) {
                    $avisar($fila, 'rh', "RH '" . $rhRaw . "' no válido (debe ser + o -). Se guardará vacío.");
                }

                $sangreRaw = $get($row, 'tipo_sangre');
                if ($sangreRaw && $limpiarTipoSangre($sangreRaw) === null) {
                    $avisar($fila, 'tipo_sangre', "Tipo de sangre '" . $sangreRaw . "' no válido. Se guardará vacío.");
                }

                $emailRaw = $get($row, 'email');
                if ($emailRaw && !filter_var($emailRaw, FILTER_VALIDATE_EMAIL)) {
                    $avisar($fila, 'email', "Correo '" . $emailRaw . "' con formato incorrecto. Se guardará tal cual.");
                }

                DB::beginTransaction();
                try {
                    $razonSocial = $get($row, 'empresa_cliente_razon_social');
                    $empresa = $razonSocial ? EmpresaCliente::firstOrCreate(
                        ['razon_social' => $razonSocial],
                        ['activo' => true]
                    ) : null;

                    $nombrePuesto = $get($row, 'puesto_trabajo_nombre');
                    $puesto = null;
                    if ($nombrePuesto && $empresa) {
                        $puesto = PuestoTrabajo::firstOrCreate(
                            ['empresa_cliente_id' => $empresa->id, 'nombre' => $nombrePuesto],
                            ['activo' => true]
                        );
                    }

                    $paciente = PacientesClinicaTrabajo::create([
                        'nombre' => $nombreCompleto,
                        'fecha_nacimiento' => $this->parseDate($get($row, 'fecha_nacimiento')),
                        'genero' => $get($row, 'genero'),
                        'estado_civil' => $get($row, 'estado_civil'),
                        'escolaridad' => $get($row, 'escolaridad'),
                        'lugar_nacimiento' => $get($row, 'lugar_nacimiento'),
                        'curp' => $curp,
                        
                        // ✅ APLICAMOS SANITIZACIÓN AQUÍ
                        'cedula_profesional' => $limpiarCedula($get($row, 'cedula_profesional')),
                        
                        'telefono' => $get($row, 'telefono'),
                        'celular' => $get($row, 'celular'),
                        'whatsapp_id' => $get($row, 'whatsapp_id'),
                        'email' => $get($row, 'email'),
                        'direccion' => $get($row, 'direccion'),
                        'contacto_emergencia' => $get($row, 'contacto_emergencia'),
                        'relacion_contacto_emergencia' => $get($row, 'relacion_contacto_emergencia'),
                        'telefono_emergencia' => $get($row, 'telefono_emergencia'),
                        
                        // ✅ APLICAMOS SANITIZACIÓN AQUÍ
                        'tipo_sangre' => $limpiarTipoSangre($get($row, 'tipo_sangre')),
                        'rh' => $limpiarRh($get($row, 'rh')),
                        
                        'alergias' => $get($row, 'alergias'),
                        'alergia_medicamentos' => $get($row, 'alergia_medicamentos'),
                        'transfusion_sanguinea' => $this->parseBoolean($get($row, 'transfusion_sanguinea')),
                        'antecedentes_medicos' => $get($row, 'antecedentes_medicos'),
                        'antecedentes_quirurgicos' => $get($row, 'antecedentes_quirurgicos'),
                        'enfermedades_cronicas' => $get($row, 'enfermedades_cronicas'),
                        'medicamentos_actuales' => $get($row, 'medicamentos_actuales'),
                        'fecha_ultima_consulta_medica' => $this->parseDate($get($row, 'fecha_ultima_consulta_medica')),
                        'motivo_ultima_consulta_medica' => $get($row, 'motivo_ultima_consulta_medica'),
                        'notas_generales' => $get($row, 'notas_generales'),
                    ]);

                    if ($empresa) {
                        DatosLaboral::create([
                            'paciente_id' => $paciente->id,
                            'empresa_cliente_id' => $empresa->id,
                            'puesto_trabajo_id' => $puesto?->id,
                            'numero_empleado' => $get($row, 'numero_empleado'),
                            'fecha_ingreso_empresa' => $this->parseDate($get($row, 'fecha_ingreso_empresa')),
                            'tipo_contrato' => $get($row, 'tipo_contrato'),
                            'turno' => $get($row, 'turno'),
                        ]);
                    }

                    DB::commit();
                    $imported++;
                } catch (\Exception $e) {
                    DB::rollBack();
                    $errors++;
                    $avisar($fila, 'general', 'No se pudo guardar esta fila. Revisa el log del sistema.');
                    Log::error('Error importando paciente en fila ' . $fila . ': ' . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            Log::error('Error general al leer archivo: ' . $e->getMessage());
            throw new \Exception('No se pudo procesar el archivo. Verifica que sea un Excel válido.');
        }

        return ['imported' => $imported, 'errors' => $errors, 'advertencias' => $advertencias];
    }

    private function parseDate(?string $value): ?string
    {
        if (!$value) return null;
        if (is_numeric($value)) {
            try { return Date::excelToDateTimeObject((float)$value)->format('Y-m-d'); } 
            catch (\Exception $e) { return null; }
        }
        $timestamp = strtotime($value);
        return $timestamp !== false ? date('Y-m-d', $timestamp) : null;
    }

    private function parseBoolean(?string $value): bool
    {
        if (!$value) return false;
        return in_array(strtolower(trim($value)), ['si', 'sí', '1', 'true', 'yes']);
    }
}