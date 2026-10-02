<?php
namespace App\Http\Controllers\ClinicaTrabajo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AudiologiaTRController extends Controller
{
    // ===== VISTAS WEB (Blade) =====
    
    /**
     * Mostrar formulario para crear nuevo examen TR
     */
    public function crear()
    {
        return view('clinica-trabajo.tr-index', [
            'titulo' => 'Nuevo Examen Médico - Trabajo Alto Riesgo',
            'modo' => 'crear'
        ]);
    }

    /**
     * Mostrar formulario para editar examen existente
     */
    public function editar($id)
    {
        $examen = DB::table('examenes_audiologia_tr')->find($id);

        if (!$examen) {
            return redirect()->route('clinica.audiologia.tr.listado')
                ->with('error', 'Examen no encontrado');
        }

        return view('clinica-trabajo.tr-index', [
            'titulo' => 'Editar Examen Médico - Trabajo Alto Riesgo',
            'modo' => 'editar',
            'examen' => $examen
        ]);
    }

    /**
     * Mostrar examen en modo lectura
     */
    public function mostrar($id)
    {
        $examen = DB::table('examenes_audiologia_tr')->find($id);

        if (!$examen) {
            abort(404, 'Examen no encontrado');
        }

        return view('clinica-trabajo.tr-index', [
            'examen' => $examen,
            'titulo' => 'Ver Examen Médico - Trabajo Alto Riesgo'
        ]);
    }
    
    /**
     * Listado de exámenes TR
     */
    public function listado(Request $request)
    {
        $filtro = $request->input('filtro', '');
        
        $query = DB::table('examenes_audiologia_tr');
        
        if ($filtro) {
            $query->where('nombre', 'like', "%$filtro%")
                  ->orWhere('empresa', 'like', "%$filtro%");
        }
        
        $examenes = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return view('clinica.audiologia.tr.listado', [
            'examenes' => $examenes,
            'titulo' => 'Listado - Exámenes TR'
        ]);
    }
    
    // ===== API (JSON) =====
    
    /**
     * Listar exámenes TR (API)
     */
    public function listar(Request $request)
    {
        $filtro = $request->input('filtro', '');
        
        $query = DB::table('examenes_audiologia_tr');
        
        if ($filtro) {
            $query->where('nombre', 'like', "%$filtro%");
        }
        
        $examenes = $query->orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $examenes,
            'total' => count($examenes)
        ]);
    }
    
    /**
     * Guardar nuevo examen TR
     */
    public function guardar(Request $request)
    {
        try {
            $data = $request->all();
            
            // Validar críticos
            $this->validate($request, [
                'hoja1.nombre' => 'required|string',
                'hoja1.empresa' => 'required|string'
            ]);
            
            $id = DB::table('examenes_audiologia_tr')->insertGetId([
                'nombre' => $data['hoja1']['nombre'] ?? null,
                'empresa' => $data['hoja1']['empresa'] ?? null,
                'riesgos' => json_encode($data['hoja1']['tipo_trabajo_riesgo'] ?? []),
                'diagnostico_principal' => $data['hoja4']['diagnosticos'][0] ?? null,
                'enfermedad_profesional' => $data['hoja4']['enfermedad_profesional'] ?? false,
                'datos_json' => json_encode($data),
                'user_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Examen guardado correctamente',
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
     * Obtener examen por ID
     */
    public function obtener($id)
    {
        $examen = DB::table('examenes_audiologia_tr')->find($id);
        
        if (!$examen) {
            return response()->json([
                'success' => false,
                'message' => 'Examen no encontrado'
            ], 404);
        }
        
        $examen->datos = json_decode($examen->datos_json, true);
        
        return response()->json([
            'success' => true,
            'data' => $examen
        ]);
    }
    
    /**
     * Actualizar examen
     */
    public function actualizar($id, Request $request)
    {
        try {
            $examen = DB::table('examenes_audiologia_tr')->find($id);
            
            if (!$examen) {
                return response()->json([
                    'success' => false,
                    'message' => 'Examen no encontrado'
                ], 404);
            }
            
            $data = $request->all();
            
            DB::table('examenes_audiologia_tr')->where('id', $id)->update([
                'nombre' => $data['hoja1']['nombre'] ?? $examen->nombre,
                'empresa' => $data['hoja1']['empresa'] ?? $examen->empresa,
                'diagnostico_principal' => $data['hoja4']['diagnosticos'][0] ?? $examen->diagnostico_principal,
                'datos_json' => json_encode($data),
                'updated_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Examen actualizado correctamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Eliminar examen
     */
    public function eliminar($id)
    {
        $examen = DB::table('examenes_audiologia_tr')->find($id);
        
        if (!$examen) {
            return response()->json([
                'success' => false,
                'message' => 'Examen no encontrado'
            ], 404);
        }
        
        DB::table('examenes_audiologia_tr')->delete($id);
        
        return response()->json([
            'success' => true,
            'message' => 'Examen eliminado correctamente'
        ]);
    }
    
    /**
     * Exportar examen a PDF
     */
    public function exportarPDF($id)
    {
        $examen = DB::table('examenes_audiologia_tr')->find($id);
        
        if (!$examen) {
            return response()->json([
                'success' => false,
                'message' => 'Examen no encontrado'
            ], 404);
        }
        
        // TODO: Implementar exportación
        return response()->json([
            'success' => true,
            'message' => 'Funcionalidad de PDF en desarrollo'
        ]);
    }
}