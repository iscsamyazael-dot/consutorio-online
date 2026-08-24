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
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ListaEsperaController extends Controller
{
    /**
     * Genera un código con formato PREFIJO-AAAA-NNNN, donde NNNN es un
     * consecutivo que se reinicia en 0001 cada vez que cambia el año.
     *
     * Mismo método que PacienteController::generarCodigoConReinicioAnual()
     * (duplicado a propósito, no compartido vía trait, para mantener el
     * mismo patrón que ya usa ese controlador).
     *
     * IMPORTANTE: debe llamarse dentro de un DB::transaction().
     */
    private function generarCodigoConReinicioAnual(string $modelo, string $columna, string $prefijo): string
    {
        $anioActual = date('Y');

        $ultimoRegistro = $modelo::where($columna, 'LIKE', "{$prefijo}-{$anioActual}-%")
            ->orderBy('id', 'desc')
            ->lockForUpdate()
            ->first();

        if ($ultimoRegistro) {
            $ultimoNumero = (int) substr($ultimoRegistro->{$columna}, -4);
            $numero = $ultimoNumero + 1;
        } else {
            $numero = 1;
        }

        return $prefijo . '-' . $anioActual . '-' . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Genera el número de turno del día, reiniciando en 1 cada vez que
     * cambia la fecha (no anual, por eso no usa el método de arriba).
     * lockForUpdate() evita que dos registros simultáneos (kiosco + sync
     * de citas, dos kioscos a la vez, o kiosco + walk-in del panel)
     * generen el mismo número de turno.
     *
     * IMPORTANTE: debe llamarse dentro de un DB::transaction().
     */
    private function generarTurnoConReinicioDiario(string $fecha): int
    {
        $ultimoRegistro = ListaEspera::where('fecha', $fecha)
            ->orderBy('id', 'desc')
            ->lockForUpdate()
            ->first();

        return $ultimoRegistro ? $ultimoRegistro->numero_turno + 1 : 1;
    }

    /**
     * Vuelca a lista_espera las citas agendadas de una fecha que aún
     * no tengan su reflejo ahí (por cita_id). No duplica.
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

            DB::transaction(function () use ($cita, $fecha) {
                $folio = $this->generarCodigoConReinicioAnual(ListaEspera::class, 'folio', 'LE');
                $numeroTurno = $this->generarTurnoConReinicioDiario($fecha);

                ListaEspera::create([
                    'folio'           => $folio,
                    'numero_turno'    => $numeroTurno,
                    'paciente_id'     => $cita->paciente_id,
                    'medico_id'       => $cita->medico_id,
                    'especialidad_id' => $cita->especialidad_id,
                    'cita_id'         => $cita->id,
                    'fecha'           => $fecha,
                    'hora_llegada'    => $cita->hora,
                    'estado'          => 'En espera',
                    'observaciones'   => 'Volcado automático desde cita agendada (' . $cita->folio . ')',
                ]);
            });
        }
    }

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
     * Agrega un walk-in (sin cita previa), usado desde el panel admin
     * por secretaria/enfermera. NO toca la tabla `citas`.
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
        $fecha = $ahora->toDateString();

        $resultado = DB::transaction(function () use ($validated, $ahora, $fecha) {

            $yaEnCola = ListaEspera::where('paciente_id', $validated['paciente_id'])
                ->where('fecha', $fecha)
                ->whereNotIn('estado', ['Finalizada', 'Cancelada'])
                ->lockForUpdate()
                ->first();

            if ($yaEnCola) {
                return ['registro' => $yaEnCola, 'ya_existia' => true];
            }

            $folio = $this->generarCodigoConReinicioAnual(ListaEspera::class, 'folio', 'LE');
            $numeroTurno = $this->generarTurnoConReinicioDiario($fecha);

            $registro = ListaEspera::create([
                'folio'           => $folio,
                'numero_turno'    => $numeroTurno,
                'paciente_id'     => $validated['paciente_id'],
                'medico_id'       => $validated['medico_id'] ?? null,
                'especialidad_id' => $validated['especialidad_id'] ?? null,
                'cita_id'         => null,
                'fecha'           => $fecha,
                'hora_llegada'    => $ahora->toTimeString(),
                'estado'          => 'En espera',
                'consultorio'     => $validated['consultorio'] ?? null,
                'observaciones'   => $validated['observaciones'] ?? 'Agregado como walk-in (sin cita previa)',
            ]);

            return ['registro' => $registro, 'ya_existia' => false];
        });

        return response()->json([
            'success'    => true,
            'registro'   => $resultado['registro']->load(['paciente', 'medico', 'especialidad']),
            'ya_existia' => $resultado['ya_existia'],
        ], $resultado['ya_existia'] ? 200 : 201);
    }

    public function show(ListaEspera $listaEspera)
    {
        return response()->json(
            $listaEspera->load(['paciente', 'medico', 'especialidad', 'cita'])
        );
    }

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

    public function destroy(ListaEspera $listaEspera)
    {
        $listaEspera->delete();

        return response()->json([
            'success' => true,
            'message' => 'Registro eliminado correctamente',
        ]);
    }

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
     * GET /api/kiosco/lista-espera-pantalla
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
                    'nombre_completo' => $nombre,
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
                $q->whereIn('nivel_urgencia', ['rojo', 'naranja'])
                  ->whereDate('created_at', $hoy);
            })
            ->count();

        return response()->json([
            'consultas_hoy' => $consultasHoy,
            'pendientes' => $pendientes,
            'urgencias' => $urgencias,
        ]);
    }

    /**
     * POST /api/kiosco/lista-espera/buscar-paciente
     */
    public function buscarParaKiosco(Request $request)
    {
        $codigo = trim($request->input('codigo', ''));
        $hoy = Carbon::now()->toDateString();

        if (str_starts_with(strtoupper($codigo), 'CIT-')) {
            $cita = Cita::where('folio', $codigo)
                ->where('fecha', $hoy)
                ->where('estado', '!=', 'Cancelada')
                ->with('paciente')
                ->first();

            if (!$cita) {
                return response()->json(['encontrado' => false, 'motivo' => 'cita_no_encontrada'], 404);
            }

            return response()->json([
                'encontrado' => true,
                'tipo'       => 'cita',
                'paciente'   => $cita->paciente,
                'cita'       => $cita,
            ]);
        }

        if (str_starts_with(strtoupper($codigo), 'PAC-')) {
            $paciente = Paciente::where('paciente_id', $codigo)->first();

            if (!$paciente) {
                return response()->json(['encontrado' => false, 'motivo' => 'paciente_no_encontrado'], 404);
            }

            $citaHoy = Cita::where('paciente_id', $paciente->id)
                ->where('fecha', $hoy)
                ->where('estado', '!=', 'Cancelada')
                ->first();

            return response()->json([
                'encontrado'    => true,
                'tipo'          => 'paciente',
                'paciente'      => $paciente,
                'cita'          => $citaHoy,
            ]);
        }

        $coincidencias = Paciente::where('nombre', 'like', "%{$codigo}%")
            ->where('estado', 'activo')
            ->limit(10)
            ->get();

        return response()->json([
            'encontrado'    => $coincidencias->count() > 0,
            'tipo'          => 'nombre',
            'coincidencias' => $coincidencias,
        ]);
    }

    /**
     * POST /api/kiosco/lista-espera/registrar-desde-kiosco
     */
    public function registrarDesdeKiosco(Request $request)
    {
        $validated = $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'cita_id'     => 'nullable|exists:citas,id',
        ]);

        $fecha = Carbon::now()->toDateString();

        $resultado = DB::transaction(function () use ($validated, $fecha) {

            $yaEnCola = ListaEspera::where('paciente_id', $validated['paciente_id'])
                ->where('fecha', $fecha)
                ->whereNotIn('estado', ['Finalizada', 'Cancelada'])
                ->lockForUpdate()
                ->first();

            if ($yaEnCola) {
                return ['registro' => $yaEnCola, 'ya_existia' => true];
            }

            $cita = $validated['cita_id'] ?? null ? Cita::find($validated['cita_id']) : null;

            $folio = $this->generarCodigoConReinicioAnual(ListaEspera::class, 'folio', 'LE');
            $numeroTurno = $this->generarTurnoConReinicioDiario($fecha);

            $registro = ListaEspera::create([
                'folio'           => $folio,
                'numero_turno'    => $numeroTurno,
                'paciente_id'     => $validated['paciente_id'],
                'medico_id'       => $cita->medico_id ?? null,
                'especialidad_id' => $cita->especialidad_id ?? null,
                'cita_id'         => $cita->id ?? null,
                'fecha'           => $fecha,
                'hora_llegada'    => Carbon::now()->toTimeString(),
                'estado'          => 'En espera',
                'observaciones'   => 'Autoregistro desde kiosco',
            ]);

            return ['registro' => $registro, 'ya_existia' => false];
        });

        return response()->json([
            'success'    => true,
            'registro'   => $resultado['registro']->load(['paciente', 'medico', 'especialidad']),
            'ya_existia' => $resultado['ya_existia'],
        ], $resultado['ya_existia'] ? 200 : 201);
    }
}