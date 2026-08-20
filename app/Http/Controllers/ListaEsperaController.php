<?php

namespace App\Http\Controllers;

use App\Models\ListaEspera;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Medico;
use App\Models\Specialty;
use App\Models\Consulta;
use App\Models\Triage;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ListaEsperaController extends Controller
{
    /**
     * Vuelca a lista_espera las citas agendadas de una fecha que aún
     * no tengan su reflejo ahí (por cita_id). No duplica.
     *
     * `citas` sigue siendo únicamente la fuente de agenda (Sofia/n8n
     * y app Ionic). lista_espera es solo un reflejo operativo del día.
     */
    private function sincronizarCitasDelDia(string $fecha)
    {
        $citasDelDia = Cita::where('fecha', $fecha)
            ->where('estado', '!=', 'Cancelada')
            ->get();

        $citaIdsYaVolcados = ListaEspera::where('fecha', $fecha)
            ->whereNotNull('cita_id')
            ->pluck('cita_id')
            ->toArray();

        foreach ($citasDelDia as $cita) {
            if (in_array($cita->id, $citaIdsYaVolcados)) {
                continue;
            }

            ListaEspera::create([
                'paciente_id'     => $cita->paciente_id,
                'medico_id'       => $cita->medico_id,
                'especialidad_id' => $cita->especialidad_id,
                'cita_id'         => $cita->id,
                'fecha'           => $fecha,
                'hora_llegada'    => $cita->hora,
                'estado'          => 'En espera',
                'observaciones'   => 'Volcado automático desde cita agendada (' . $cita->folio . ')',
            ]);
        }
    }

    /**
     * GET /lista-espera
     * Lista la cola de atención de un día (agendados + walk-ins).
     * Filtros opcionales: fecha (default hoy), medico_id, especialidad_id, estado.
     */
    public function index(Request $request)
    {
        $fecha = $request->filled('fecha') ? $request->fecha : Carbon::now()->toDateString();

        $this->sincronizarCitasDelDia($fecha);

        $query = ListaEspera::with(['paciente', 
                                    'medico', 
                                    'especialidad', 
                                    'cita',
                                    'paciente.ultimoTriage'])
            ->where('fecha', $fecha);
        

        // Si viene "todas_fechas=1", no se filtra por fecha (se listan todos
        // los registros). Si no viene, se filtra por la fecha indicada (o hoy).
        if (!$request->boolean('todas_fechas')) {
            $query->where('fecha', $fecha);
        }

        if ($request->filled('medico_id')) {
            $query->where('medico_id', $request->medico_id);
        }

        if ($request->filled('especialidad_id')) {
            $query->where('especialidad_id', $request->especialidad_id);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        return response()->json(
            $query->orderBy('numero_turno')->get()
        );
    }

    /**
     * GET /lista-espera/create
     * Devuelve los catálogos necesarios para poblar el formulario de
     * "agregar walk-in" en el frontend (mismo patrón que
     * CitaController@create): pacientes activos, médicos activos y
     * especialidades activas.
     */
    public function create()
    {
        $pacientes = Paciente::select('id', 'nombre')
            ->where('estado', 'activo')
            ->get();

        $medicos = Medico::select('id', 'nombre', 'especialidad_id')
            ->where('activo', 1)
            ->get();

        $especialidades = Specialty::select('id', 'nombre')
            ->where('estado', 'Activo')
            ->get();

        return response()->json([
            'pacientes'      => $pacientes,
            'medicos'        => $medicos,
            'especialidades' => $especialidades,
        ]);
    }

    /**
     * POST /lista-espera
     * Agrega un walk-in (sin cita previa). NO toca la tabla `citas`.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'paciente_id'     => 'required|exists:pacientes,id',
            'medico_id'       => 'nullable|exists:medicos,id',
            'especialidad_id' => 'nullable|exists:especialidades,id',
            'consultorio'     => 'nullable|string|max:50',
            'observaciones'   => 'nullable|string',
        ]);

        $ahora = Carbon::now();

        $registro = ListaEspera::create([
            'paciente_id'     => $validated['paciente_id'],
            'medico_id'       => $validated['medico_id'] ?? null,
            'especialidad_id' => $validated['especialidad_id'] ?? null,
            'cita_id'         => null,
            'fecha'           => $ahora->toDateString(),
            'hora_llegada'    => $ahora->toTimeString(),
            'estado'          => 'En espera',
            'consultorio'     => $validated['consultorio'] ?? null,
            'observaciones'   => $validated['observaciones'] ?? 'Agregado como walk-in (sin cita previa)',
        ]);

        return response()->json([
            'success'  => true,
            'registro' => $registro->load(['paciente', 'medico', 'especialidad']),
        ], 201);
    }

    /**
     * GET /lista-espera/{listaEspera}
     */
    public function show(ListaEspera $listaEspera)
    {
        return response()->json(
            $listaEspera->load(['paciente', 'medico', 'especialidad', 'cita'])
        );
    }

      /**
     * GET /lista-espera/{listaEspera}/edit
     * Igual que create(), pero además incluye el registro actual, por
     * si el frontend quiere precargar un formulario de edición con
     * los catálogos + los valores ya guardados.
     */
    public function edit(ListaEspera $listaEspera)
    {
        $pacientes = Paciente::select('id', 'nombre')
            ->where('estado', 'activo')
            ->get();

        $medicos = Medico::select('id', 'nombre', 'especialidad_id')
            ->where('activo', 1)
            ->get();

        $especialidades = Specialty::select('id', 'nombre')
            ->where('estado', 'Activo')
            ->get();

        return response()->json([
            'registro'       => $listaEspera->load(['paciente', 'medico', 'especialidad']),
            'pacientes'      => $pacientes,
            'medicos'        => $medicos,
            'especialidades' => $especialidades,
        ]);
    }


    /**
     * PUT/PATCH /lista-espera/{listaEspera}
     * Actualización general del registro (médico, especialidad,
     * consultorio, observaciones). Para el cambio de estado se usa
     * la ruta dedicada actualizarEstado() (más abajo), para poder
     * validar solo ese campo sin exigir el resto del payload.
     */
    public function update(Request $request, ListaEspera $listaEspera)
    {
        $validated = $request->validate([
            'medico_id'       => 'nullable|exists:medicos,id',
            'especialidad_id' => 'nullable|exists:especialidades,id',
            'consultorio'     => 'nullable|string|max:50',
            'observaciones'   => 'nullable|string',
        ]);

        $listaEspera->update($validated);

        return response()->json([
            'success'  => true,
            'registro' => $listaEspera->load(['paciente', 'medico', 'especialidad']),
        ]);
    }

    /**
     * DELETE /lista-espera/{listaEspera}
     * Elimina un registro (por ejemplo, si se agregó un walk-in por error).
     */
    public function destroy(ListaEspera $listaEspera)
    {
        $listaEspera->delete();

        return response()->json([
            'success' => true,
            'message' => 'Registro eliminado correctamente',
        ]);
    }

    /**
     * PATCH /lista-espera/{listaEspera}/estado
     * Cambia únicamente el estado del turno.
     */
    public function actualizarEstado(Request $request, ListaEspera $listaEspera)
    {
        $request->validate([
            'estado' => 'required|in:En espera,Llamando,En proceso,Finalizada,Cancelada',
        ]);

        $listaEspera->estado = $request->estado;
        $listaEspera->save();

        return response()->json([
            'success'  => true,
            'registro' => $listaEspera,
        ]);
    }

    /**
     * GET /lista-espera-pantalla
     * Endpoint público de solo lectura para la pantalla de TV.
     * Solo número de turno, nombre truncado, consultorio y estado.
     */
    public function pantalla()
    {
        $fecha = Carbon::now()->toDateString();

        $registros = ListaEspera::with('paciente')
            ->where('fecha', $fecha)
            ->whereIn('estado', ['En espera', 'Llamando', 'En proceso'])
            ->orderBy('numero_turno')
            ->get()
            ->map(function ($r) {
                $nombre = $r->paciente->nombre ?? '';
                $partes = explode(' ', trim($nombre));
                $primerNombre = $partes[0] ?? '';
                $inicialApellido = isset($partes[1]) ? mb_strtoupper(mb_substr($partes[1], 0, 1)) . '.' : '';

                return [
                    'numero_turno' => $r->numero_turno,
                    'nombre_corto' => trim($primerNombre . ' ' . $inicialApellido),
                    'consultorio'  => $r->consultorio,
                    'estado'       => $r->estado,
                ];
            });

        return response()->json($registros);
    }

    public function resumen()
    {   
        $hoy = today();
        
        $consultasHoy = Consulta::where('estado', 'Finalizada')
            ->whereDate('created_at', $hoy)
            ->count();

        $pendientes = ListaEspera::where('estado', 'En espera')
            ->whereDate('created_at', $hoy)
            ->count();

        $urgencias = ListaEspera::where('estado', '!=', 'Cancelada')
        ->whereDate('fecha', $hoy)
        ->whereHas('paciente.triages', function ($q) use ($hoy) {
            $q->where('nivel_urgencia',  ['rojo', 'naranja'])
              ->whereDate('created_at', $hoy);
            })
        ->count();

        return response()->json([
            'consultas_hoy' => $consultasHoy,
            'pendientes' => $pendientes,
            'urgencias' => $urgencias,
        ]);
    }
}