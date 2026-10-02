<?php
namespace App\Http\Controllers\ClinicaTrabajo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AudiologiaDELLIController extends Controller
{
    // ===== VISTAS WEB (Blade) =====
    
    /**
     * Mostrar formulario para crear nueva ficha
     */
    public function crear()
    {
        return view('clinica-trabajo.delli-index', [
            'titulo' => 'Nueva Historia Clínica Audiológica',
            'modo' => 'crear'
        ]);
    }

    /**
     * Mostrar formulario para editar ficha existente
     */
    public function editar($id)
    {
        $ficha = DB::table('fichas_audiologia_delli')->find($id);

        if (!$ficha) {
            return redirect()->route('clinica.audiologia.delli.listado')
                ->with('error', 'Ficha no encontrada');
        }

        return view('clinica-trabajo.delli-index', [
            'titulo' => 'Editar Historia Clínica Audiológica',
            'modo' => 'editar',
            'ficha' => $ficha
        ]);
    }

    /**
     * Mostrar ficha en modo lectura
     */
    public function mostrar($id)
    {
        $ficha = DB::table('fichas_audiologia_delli')->find($id);

        if (!$ficha) {
            abort(404, 'Ficha no encontrada');
        }

        return view('clinica-trabajo.delli-index', [
            'ficha' => $ficha,
            'titulo' => 'Ver Historia Clínica Audiológica'
        ]);
    }
    
    /**
     * Listado de fichas DELLI
     */
    public function listado(Request $request)
    {
        $filtro = $request->input('filtro', '');
        $pagina = $request->input('pagina', 1);
        
        $query = DB::table('fichas_audiologia_delli');
        
        if ($filtro) {
            $query->where('nombre', 'like', "%$filtro%")
                  ->orWhere('folio', 'like', "%$filtro%");
        }
        
        $fichas = $query->orderBy('created_at', 'desc')
                        ->paginate(15);
        
        return view('clinica.audiologia.delli.listado', [
            'fichas' => $fichas,
            'titulo' => 'Listado - Historia Clínica Audiológica (DELLI)'
        ]);
    }
    
    // ===== API (JSON) =====
    
    /**
     * Listar fichas DELLI (API)
     */
    public function listar(Request $request)
    {
        $filtro = $request->input('filtro', '');
        $empresa = $request->input('empresa', '');
        
        $query = DB::table('fichas_audiologia_delli');
        
        if ($filtro) {
            $query->where('nombre', 'like', "%$filtro%");
        }
        if ($empresa) {
            $query->where('empresa_actual', 'like', "%$empresa%");
        }
        
        $fichas = $query->orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $fichas,
            'total' => count($fichas)
        ]);
    }
    
    /**
     * Guardar nueva ficha DELLI (POST)
     */
    public function guardar(Request $request)
    {
        try {
            $data = $request->all();
            
            // Validar datos críticos
            $this->validate($request, [
                'hoja1.nombre' => 'required|string',
                'hoja1.empresa_actual' => 'required|string',
                'hoja4.aptitud' => 'required|string'
            ]);
            
            // Asignar usuario actual
            $data['user_id'] = Auth::id();
            $data['created_at'] = now();
            
            // Guardar JSON completo
            $id = DB::table('fichas_audiologia_delli')->insertGetId([
                'folio' => $data['folio'] ?? 'AUD-' . date('Y') . '-' . rand(1000, 9999),
                'nombre' => $data['hoja1']['nombre'] ?? null,
                'fecha_nacimiento' => $data['hoja1']['fecha_nacimiento'] ?? null,
                'empresa_actual' => $data['hoja1']['empresa_actual'] ?? null,
                'riesgo_auditivo' => $data['hoja2']['riesgo_auditivo'] ?? null,
                'pta_promedio' => $data['hoja4']['pta_promedio'] ?? 0,
                'clasificacion' => $data['hoja4']['clasificacion'] ?? null,
                'aptitud' => $data['hoja4']['aptitud'] ?? null,
                'datos_json' => json_encode($data),
                'user_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Ficha guardada correctamente',
                'folio' => $data['folio'],
                'id' => $id
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener una ficha por ID (GET)
     */
    public function obtener($id)
    {
        $ficha = DB::table('fichas_audiologia_delli')->find($id);
        
        if (!$ficha) {
            return response()->json([
                'success' => false,
                'message' => 'Ficha no encontrada'
            ], 404);
        }
        
        // Decodificar JSON
        $ficha->datos = json_decode($ficha->datos_json, true);
        
        return response()->json([
            'success' => true,
            'data' => $ficha
        ]);
    }
    
    /**
     * Actualizar ficha (PUT)
     */
    public function actualizar($id, Request $request)
    {
        try {
            $ficha = DB::table('fichas_audiologia_delli')->find($id);
            
            if (!$ficha) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ficha no encontrada'
                ], 404);
            }
            
            $data = $request->all();
            
            DB::table('fichas_audiologia_delli')->where('id', $id)->update([
                'nombre' => $data['hoja1']['nombre'] ?? $ficha->nombre,
                'empresa_actual' => $data['hoja1']['empresa_actual'] ?? $ficha->empresa_actual,
                'riesgo_auditivo' => $data['hoja2']['riesgo_auditivo'] ?? $ficha->riesgo_auditivo,
                'pta_promedio' => $data['hoja4']['pta_promedio'] ?? $ficha->pta_promedio,
                'aptitud' => $data['hoja4']['aptitud'] ?? $ficha->aptitud,
                'datos_json' => json_encode($data),
                'updated_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Ficha actualizada correctamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Eliminar ficha (DELETE)
     */
    public function eliminar($id)
    {
        $ficha = DB::table('fichas_audiologia_delli')->find($id);
        
        if (!$ficha) {
            return response()->json([
                'success' => false,
                'message' => 'Ficha no encontrada'
            ], 404);
        }
        
        DB::table('fichas_audiologia_delli')->delete($id);
        
        return response()->json([
            'success' => true,
            'message' => 'Ficha eliminada correctamente'
        ]);
    }
    
    /**
     * Generar folio automático
     */
    public function generarFolio()
    {
        $ano = date('Y');
        $numero = rand(1000, 9999);
        $folio = "AUD-{$ano}-{$numero}";
        
        return response()->json([
            'success' => true,
            'folio' => $folio
        ]);
    }
    
    /**
     * Exportar ficha a PDF
     */
    public function exportarPDF($id)
    {
        $ficha = DB::table('fichas_audiologia_delli')->find($id);
        
        if (!$ficha) {
            return response()->json([
                'success' => false,
                'message' => 'Ficha no encontrada'
            ], 404);
        }
        
        // TODO: Implementar exportación con HTML2PDF o DOMPDF
        // Por ahora retornar placeholder
        return response()->json([
            'success' => true,
            'message' => 'Funcionalidad de PDF en desarrollo',
            'url' => '#'
        ]);
    }
}




# ========================================
# NOTA IMPORTANTE
# ========================================
# Los controllers anteriores usan SQL directo (DB::table).
# Asegúrate de que las tablas existan con la siguiente estructura:

# Tabla: fichas_audiologia_delli
# - id (PRIMARY KEY)
# - folio (VARCHAR, UNIQUE)
# - nombre (VARCHAR)
# - fecha_nacimiento (DATE)
# - empresa_actual (VARCHAR)
# - riesgo_auditivo (VARCHAR)
# - pta_promedio (DECIMAL)
# - clasificacion (VARCHAR)
# - aptitud (VARCHAR) — **REQUERIDO**
# - datos_json (LONGTEXT) — Guarda toda la estructura JSON
# - user_id (INTEGER, FK users)
# - created_at, updated_at (TIMESTAMP)

# Tabla: examenes_audiologia_tr
# - id (PRIMARY KEY)
# - nombre (VARCHAR)
# - empresa (VARCHAR)
# - riesgos (JSON)
# - diagnostico_principal (TEXT)
# - enfermedad_profesional (BOOLEAN)
# - datos_json (LONGTEXT)
# - user_id (INTEGER, FK users)
# - created_at, updated_at (TIMESTAMP)