<?php

namespace App\Imports\ClinicaTrabajo;

use App\Models\ClinicaTrabajo\EmpresaCliente;
use App\Models\ClinicaTrabajo\PuestoTrabajo;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;

class EmpresasYPuestosImport
{
    /**
     * Procesa un archivo de Excel con dos hojas: Empresas y Puestos.
     *
     * @param string $filePath Ruta absoluta al archivo subido (ej: $request->file('archivo')->getPathname())
     * @return array Estadísticas detalladas del proceso
     */
    public function process(string $filePath): array
    {
        $stats = [
            'empresas_importadas' => 0,
            'empresas_errores' => 0,
            'puestos_importados' => 0,
            'puestos_errores' => 0,
        ];

        try {
            // IOFactory detecta automáticamente si es .xlsx, .xls o .csv
            $spreadsheet = IOFactory::load($filePath);

            // 1. Procesar hoja "Empresas"
            if ($spreadsheet->sheetNameExists('Empresas')) {
                $worksheet = $spreadsheet->getSheetByName('Empresas');
                $rows = $worksheet->toArray();
                $empresasStats = $this->processEmpresas($rows);
                $stats['empresas_importadas'] = $empresasStats['imported'];
                $stats['empresas_errores'] = $empresasStats['errors'];
            } else {
                Log::warning('No se encontró la hoja "Empresas" en el archivo de importación.');
            }

            // 2. Procesar hoja "Puestos"
            if ($spreadsheet->sheetNameExists('Puestos')) {
                $worksheet = $spreadsheet->getSheetByName('Puestos');
                $rows = $worksheet->toArray();
                $puestosStats = $this->processPuestos($rows);
                $stats['puestos_importados'] = $puestosStats['imported'];
                $stats['puestos_errores'] = $puestosStats['errors'];
            } else {
                Log::warning('No se encontró la hoja "Puestos" en el archivo de importación.');
            }

        } catch (\Exception $e) {
            Log::error('Error general al leer el archivo de importación: ' . $e->getMessage());
            throw new \Exception('No se pudo procesar el archivo. Verifica que sea un Excel válido y no esté corrupto.');
        }

        return $stats;
    }

    /**
     * Procesa la hoja de Empresas.
     */
    private function processEmpresas(array $rows): array
    {
        $imported = 0;
        $errors = 0;

        if (empty($rows)) {
            return ['imported' => 0, 'errors' => 0];
        }

        // La primera fila son los encabezados. La extraemos y creamos un mapa de índices.
        $headers = array_shift($rows);
        $headerMap = [];
        foreach ($headers as $index => $header) {
            $headerMap[strtolower(trim($header))] = $index;
        }

        foreach ($rows as $rowIndex => $row) {
            // Saltar filas completamente vacías
            if (empty(array_filter($row))) {
                continue;
            }

            $razonSocial = trim($row[$headerMap['razon_social']] ?? '');
            if (empty($razonSocial)) {
                continue;
            }

            $rfc = strtoupper(trim($row[$headerMap['rfc']] ?? ''));
            
            try {
                // Buscar por RFC si existe, si no, por razón social
                $query = $rfc ? ['rfc' => $rfc] : ['razon_social' => $razonSocial];

                EmpresaCliente::updateOrCreate(
                    $query,
                    [
                        'razon_social'      => $razonSocial,
                        'giro_actividad'    => trim($row[$headerMap['giro_actividad']] ?? ''),
                        'direccion'         => trim($row[$headerMap['direccion']] ?? ''),
                        'contacto_nombre'   => trim($row[$headerMap['contacto_nombre']] ?? ''),
                        'contacto_telefono' => trim($row[$headerMap['contacto_telefono']] ?? ''),
                        'contacto_email'    => trim($row[$headerMap['contacto_email']] ?? ''),
                        'activo'            => $this->parseBoolean($row[$headerMap['activo']] ?? '1'),
                    ]
                );
                $imported++;
            } catch (\Exception $e) {
                $errors++;
                // +2 porque $rowIndex empieza en 0 y saltamos la fila 1 de encabezados
                Log::error('Error importando empresa en fila ' . ($rowIndex + 2) . ': ' . $e->getMessage());
            }
        }

        return ['imported' => $imported, 'errors' => $errors];
    }

    /**
     * Procesa la hoja de Puestos.
     */
    private function processPuestos(array $rows): array
    {
        $imported = 0;
        $errors = 0;

        if (empty($rows)) {
            return ['imported' => 0, 'errors' => 0];
        }

        // La primera fila son los encabezados.
        $headers = array_shift($rows);
        $headerMap = [];
        foreach ($headers as $index => $header) {
            $headerMap[strtolower(trim($header))] = $index;
        }

        foreach ($rows as $rowIndex => $row) {
            if (empty(array_filter($row))) {
                continue;
            }

            $razonSocialEmpresa = trim($row[$headerMap['razon_social_empresa']] ?? '');
            $nombrePuesto = trim($row[$headerMap['nombre_puesto']] ?? '');

            if (empty($razonSocialEmpresa) || empty($nombrePuesto)) {
                continue;
            }

            try {
                // 1. Buscar la empresa por su razón social exacta
                $empresa = EmpresaCliente::where('razon_social', $razonSocialEmpresa)->first();

                if (!$empresa) {
                    Log::warning("Puesto ignorado: No se encontró la empresa '{$razonSocialEmpresa}' en la fila " . ($rowIndex + 2));
                    continue;
                }

                // 2. Crear o actualizar el puesto de trabajo vinculado a esa empresa
                PuestoTrabajo::updateOrCreate(
                    [
                        'empresa_cliente_id' => $empresa->id,
                        'nombre'             => $nombrePuesto,
                    ],
                    [
                        'departamento' => trim($row[$headerMap['departamento']] ?? ''),
                        'activo'       => true,
                    ]
                );
                $imported++;
            } catch (\Exception $e) {
                $errors++;
                Log::error('Error importando puesto en fila ' . ($rowIndex + 2) . ': ' . $e->getMessage());
            }
        }

        return ['imported' => $imported, 'errors' => $errors];
    }

    /**
     * Convierte valores comunes de texto a booleano.
     */
    private function parseBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        return in_array(strtolower(trim((string)$value)), ['1', 'true', 'yes', 'si', 'sí', 'activo']);
    }
}