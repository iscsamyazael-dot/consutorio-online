<?php

namespace App\Imports\ClinicaTrabajo;

use App\Models\ClinicaTrabajo\EmpresaCliente;
use App\Models\ClinicaTrabajo\PuestoTrabajo;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;

class PuestosImport
{
    /**
     * Procesa un archivo de Excel/CSV y crea o actualiza puestos de trabajo.
     *
     * @param string $filePath Ruta absoluta al archivo subido (ej: $request->file('archivo')->getPathname())
     * @return array Estadísticas del proceso
     */
    public function process(string $filePath): array
    {
        $imported = 0;
        $errors = 0;

        try {
            // IOFactory detecta automáticamente si es .xlsx, .xls o .csv
            $spreadsheet = IOFactory::load($filePath);
            
            // Intentamos buscar la hoja "Puestos". Si no existe, usamos la hoja activa.
            $worksheet = $spreadsheet->sheetNameExists('Puestos') 
                ? $spreadsheet->getSheetByName('Puestos') 
                : $spreadsheet->getActiveSheet();
                
            $rows = $worksheet->toArray();

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

                $razonSocialEmpresa = trim($row[$headerMap['razon_social_empresa']] ?? '');
                $nombrePuesto = trim($row[$headerMap['nombre_puesto']] ?? '');

                // Validamos que tengamos los datos mínimos
                if (empty($razonSocialEmpresa) || empty($nombrePuesto)) {
                    continue;
                }

                try {
                    // 1. Buscar la empresa por su razón social exacta
                    $empresa = EmpresaCliente::where('razon_social', $razonSocialEmpresa)->first();

                    if (!$empresa) {
                        Log::warning("Puesto ignorado: No se encontró la empresa '{$razonSocialEmpresa}' en la fila " . ($rowIndex + 2));
                        continue; // Saltamos esta fila, pero seguimos con el resto del archivo
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
                    // +2 porque $rowIndex empieza en 0 y saltamos la fila 1 de encabezados
                    Log::error('Error importando puesto en fila ' . ($rowIndex + 2) . ': ' . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            Log::error('Error general al leer el archivo de puestos: ' . $e->getMessage());
            throw new \Exception('No se pudo procesar el archivo. Verifica que sea un Excel o CSV válido y no esté corrupto.');
        }

        return [
            'imported' => $imported,
            'errors' => $errors
        ];
    }
}