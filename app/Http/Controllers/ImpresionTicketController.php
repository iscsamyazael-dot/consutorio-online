<?php

namespace App\Http\Controllers;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector; // o el conector que uses (WindowsPrintConnector, etc.)
use Mike42\Escpos\EscposImage;
use App\Models\ConfiguracionImpresora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ImpresionTicketController extends Controller
{
    // Comandos ESC/POS estándar, funcionan en la gran mayoría de
    // impresoras térmicas económicas (Xprinter, Rongta, HPRT, etc.)
    private const ESC = "\x1B";
    private const GS  = "\x1D";

    /**
     * POST /api/kiosco/imprimir-ticket
     * Arma el ticket del turno en formato ESC/POS y lo envía por socket
     * TCP directo a la impresora configurada en configuracion_impresora.
     * No usa window.print() ni ninguna API del navegador — por eso no
     * dispara ningún diálogo de impresión en el kiosco.
     */
    public function imprimirTicket(Request $request)
    {
        $validated = $request->validate([
            'folio'         => 'required|string',
            'numero_turno'  => 'required|string',
            'nombre_paciente' => 'required|string',
            'hora'          => 'nullable|string',
        ]);

        $impresora = ConfiguracionImpresora::where('activo', 1)->first();

        if (!$impresora) {
            return response()->json([
                'success' => false,
                'error'   => 'No hay ninguna impresora configurada.',
            ], 422);
        }

        $ticket = $this->armarTicketEscPos($validated, $impresora->ancho_papel_mm);

        try {
            $socket = @fsockopen($impresora->ip, $impresora->puerto, $errno, $errstr, 5);

            if (!$socket) {
                Log::warning("No se pudo conectar a la impresora {$impresora->ip}:{$impresora->puerto} — $errstr ($errno)");
                return response()->json([
                    'success' => false,
                    'error'   => 'No se pudo conectar con la impresora.',
                ], 502);
            }

            fwrite($socket, $ticket);
            fclose($socket);

            return response()->json(['success' => true]);

        } catch (\Throwable $e) {
            Log::error("Error al imprimir ticket: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error'   => 'Ocurrió un error al enviar el ticket a la impresora.',
            ], 500);
        }
    }

    /**
     * 1. FUNCIÓN CENTRAL DE DISEÑO (Aquí haces todos tus cambios de texto, 
     * orden, guiones y espacios una sola vez).
     */
    private function obtenerLineasTicket(array $datos): array
    {   
        // Consultamos la configuración general de la empresa desde tu modelo
        $empresa = \App\Models\Empresa::first(); 
        
        $nombreEmpresa  = $empresa->nombre_empresa ?? 'CLINICA MEDICA';
        $direccionEmpresa = $empresa->direccion ?? 'Yucatán, México';
        $telefonoEmpresa  = $empresa->telefono ?? '';
        
        $turno = $datos['numero_turno'];
        $folio = $datos['folio'];
        $paciente = $datos['nombre_paciente'];
        $horaCruda = $datos['hora'] ?? date('H:i:s');
        $horaFormateada = date('h:i:s A', strtotime($horaCruda));

        $lineas = [
            str_pad("          [ LOGO EMPRESA ]          ", 34, " ", STR_PAD_BOTH),
            str_pad(mb_strtoupper($nombreEmpresa), 34, " ", STR_PAD_BOTH),
            str_pad("COMPROBANTE DE TURNO", 34, " ", STR_PAD_BOTH),
            "----------------------------------",
            "",
            str_pad("TURNO ASIGNADO", 34, " ", STR_PAD_BOTH),
            str_pad("[ " . $turno . " ]", 34, " ", STR_PAD_BOTH),
            "",
            "----------------------------------",
            str_pad("Folio:", 10, " ", STR_PAD_RIGHT) . str_pad($folio, 24, " ", STR_PAD_RIGHT),
            str_pad("Paciente:", 10, " ", STR_PAD_RIGHT) . str_pad(substr($paciente, 0, 24), 24, " ", STR_PAD_RIGHT),
        ];

        if (!empty($horaFormateada)) {
            $lineas[] = str_pad("Hora:", 10, " ", STR_PAD_RIGHT) . str_pad($horaFormateada, 24, " ", STR_PAD_RIGHT);
        }

        $lineas[] = "----------------------------------";
        $lineas[] = str_pad("Toma asiento, te llamaremos", 34, " ", STR_PAD_BOTH);
        $lineas[] = str_pad("en pantalla.", 34, " ", STR_PAD_BOTH);
        $lineas[] = "";
        $lineas[] = str_pad("¡Gracias por tu visita!", 34, " ", STR_PAD_BOTH);
        $lineas[] = "";
        $lineas[] = "----------------------------------";

        // Usamos la dirección de la tabla configuración_empresa
     // 📝 DIRECCIÓN DINÁMICA CON SALTO DE LÍNEA AUTOMÁTICO (Wrap)
        if (!empty($direccionEmpresa)) {
            // Corta el texto si pasa de 32 caracteres por renglón
            $direccionFormateada = wordwrap($direccionEmpresa, 32, "\n", true);
            $lineasDireccion = explode("\n", $direccionFormateada);

            // Centramos cada renglón resultante de la dirección
            foreach ($lineasDireccion as $lineaDir) {
                $lineas[] = str_pad(trim($lineaDir), 32, " ", STR_PAD_RIGHT);
            }
        }

        if (!empty($telefonoEmpresa)) {
           $lineas[] = " " . str_pad("Tel: " . $telefonoEmpresa, 32, " ", STR_PAD_RIGHT);
        }

        return $lineas;
    }

    /**
     * 2. Endpoint de previsualización (Usa el diseño central para la pantalla)
     */
    public function previewTextoPlano(Request $request)
    {
        $validated = $request->validate([
            'folio'           => 'required|string',
            'numero_turno'    => 'required',
            'nombre_paciente' => 'required|string',
            'hora'            => 'nullable|string',
        ]);

        $lineas = $this->obtenerLineasTicket($validated);

        return response()->json([
            'success' => true,
            'layout_texto' => implode("\n", $lineas)
        ]);
    }

    /**
     * 3. Tu función original ESC/POS para la impresora física 
     * (Cuando llegue la impresora, puede consumir la misma base de líneas o adaptarse)
     */
    private function armarTicketEscPos(array $datos, int $anchoPapelMm): string
    {   
        // O si estás usando sockets o generando la cadena de bytes directamente:
        $empresa = \App\Models\Empresa::first();
        $logoUrl = $empresa ? $empresa->logo_url : null; // Ej: 'storage/logos/empresa.png'
        $lineas = $this->obtenerLineasTicket($datos);
        $ticket = '';
        
        $ticket  = self::ESC . '@'; // Inicializar impresora
        $ticket .= self::ESC . 'a' . chr(1); // Centrar
        
        // 🖼️ SI HAY UN LOGO CONFIGURADO, INTENTAMOS INTEGRARLO
        if (!empty($logoUrl)) {
            try {
                // Ruta física de la imagen en tu servidor (ajusta según guardes tus archivos en storage)
                $rutaImagen = public_path($logoUrl); // o storage_path('app/public/' . str_replace('storage/', '', $logoUrl));

                if (file_exists($rutaImagen)) {
                    // Nota: Si usas la librería mike42/escpos-php, cargarías la imagen así:
                    // $tpmImg = EscposImage::load($rutaImagen, false);
                    // $printer->bitImage($tpmImg);
                    
                    // Si estás armando los bytes a mano mediante raw ESC/POS, aquí iría 
                    // la rutina de conversión a raster (GS v 0). Para evitar errores de 
                    // compatibilidad de drivers térmicos, las librerías como mike42 hacen esto por ti.
                }
            } catch (\Exception $e) {
                // Si falla la carga del logo, la impresión continúa normalmente con el texto
                \Log::warning('No se pudo imprimir el logo térmico: ' . $e->getMessage());
            }
        }

        foreach ($lineas as $index => $linea) {
            // Aquí aplicas los comandos ESC/POS de negrita o tamaño grande 
            // según la línea que corresponda (por ejemplo, destacar el turno)
            if ($index == 5) { // Línea del turno
                $ticket .= self::GS . '!' . chr(0x11); // doble alto y ancho
                $ticket .= $linea . "\n";
                $ticket .= self::GS . '!' . chr(0x00); // normal
            } else {
                $ticket .= $linea . "\n";
            }
        }

        // Espacios finales y corte
        $ticket .= "\n\n\n";
        $ticket .= self::GS . 'V' . chr(1);

        return $ticket;
    }

}