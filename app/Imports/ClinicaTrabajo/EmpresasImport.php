<?php

namespace App\Imports\ClinicaTrabajo;

use App\Models\ClinicaTrabajo\EmpresaCliente;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;

class EmpresasImport
{
    /**
     * Procesa un archivo de Excel/CSV y actualiza o crea empresas.
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
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // La primera fila son los encabezados. La extraemos y la eliminamos del array de datos.
            $headers = array_shift($rows);
            
            // Creamos un mapa de índices para que el orden de las columnas no sea rígido
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
                
                // Si no hay razón social, saltamos la fila
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
        } catch (\Exception $e) {
            Log::error('Error general al leer el archivo de empresas: ' . $e->getMessage());
            throw new \Exception('No se pudo procesar el archivo. Verifica que sea un Excel o CSV válido y no esté corrupto.');
        }

        return [
            'imported' => $imported,
            'errors' => $errors
        ];
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