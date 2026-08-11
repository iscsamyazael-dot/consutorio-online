<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use App\Models\Triage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class PacienteController extends Controller
{
    /**
     * API: Carga rápida para tabla con paginación/límite
     */
    public function index()
    {
        // NOTA: Traer TODO sin límite frenará tu app. 
        // Si usas datatables o Vue, se recomienda paginar o seleccionar campos específicos.
        // return Paciente::with(['triages' => function ($q) {
        //     $q->latest()->limit(1); // Opcional: solo traer el último triage si no necesitas todo el historial aquí
        // }])
        // ->select('id', 'paciente_id', 'nombre', 'apellido_paterno', 'apellido_materno', 'telefono')
        // ->latest('id')
        // ->paginate(15); 
         return Paciente::with(['triages'])->get();
    }

    /**
     * Búsqueda ultrarrápida tipo Autocomplete (input de búsqueda ligera)
     */
    public function buscar(Request $request)
    {
        $texto = $request->texto;

        return Paciente::select(
                'id',
                'nombre',
            )
            ->where(function ($query) use ($texto) {
                $query->where('nombre', 'like', "%{$texto}%");
            })
            ->limit(8)
            ->get()
            ->map(function ($paciente) {
                $paciente->nombre_completo = $paciente->nombre;

                return $paciente;
            });
    }

    /**
     * Filtro optimizado sin CONCAT para aprovechar índices de MySQL
     */
    public function filtrar_paciente(Request $request)
    {
        $buscar = trim($request->get('buscar'));

        if (empty($buscar)) {
            return response()->json([]);
        }

        $match = strlen($buscar) < 3 ? "{$buscar}%" : "%{$buscar}%";

        return Paciente::select('id', 'paciente_id', 'nombre')
            ->where('paciente_id', 'like', $match)
            ->orWhere('nombre', 'like', $match)
            ->limit(10)
            ->get();
    }

    public function lista()
    {
        $totalPacientes = Paciente::count();

        $camposObligatorios = [
            'nombre', 'telefono', 'email', 'sexo',
            'fecha_nacimiento', 'curp', 'direccion',
            'contacto_emergencia', 'telefono_emergencia', 'tipo_sangre',
        ];

        $pacientesPendientes = Paciente::where(function ($query) use ($camposObligatorios) {
            foreach ($camposObligatorios as $campo) {
                $query->orWhereNull($campo)->orWhere($campo, '');
            }
        })->get();

        return view('pacientes.index', compact(
            'totalPacientes',
            'pacientesPendientes'
        ))->with('totalPendientes', $pacientesPendientes->count());
    }


    public function create($id = null)
    {
        $paciente = null;

        if ($id) {
            $paciente = Paciente::findOrFail($id);
        }

        return view('pacientes.create', compact('paciente'));
    }

    /**
     * Guarda un paciente nuevo junto con su primer triage.
     *
     * Los códigos (paciente_id, triage_codigo) se generan dentro de una
     * transacción con lockForUpdate para que el consecutivo reinicie
     * correctamente cada año y no se dupliquen si dos altas ocurren
     * al mismo tiempo (ver generarCodigoConReinicioAnual()).
     */
    public function store(Request $request)
    {
        try {
            $resultado = DB::transaction(function () use ($request) {

                // Generamos el código del paciente (PAC-AÑO-0001)
                $clave = $this->generarCodigoConReinicioAnual(Paciente::class, 'paciente_id', 'PAC');

                $paciente = Paciente::create([
                    'paciente_id' => $clave,
                    'nombre' => $request->nombre,
                    'telefono' => $request->telefono,
                    'email' => $request->email,
                    'edad' => $request->edad_anios, // Guardamos la edad en años
                    'sexo' => $request->sexo,
                    'direccion' => $request->direccion,
                    'tipo_sangre' => $request->tipo_sangre,
                    'contacto_emergencia' => $request->contacto_emergencia,
                    'telefono_emergencia' => $request->telefono_emergencia,
                    'curp' => $request->curp,
                    'estado' => $request->estado,
                    'foto' => "null", // Guardamos la ruta de la foto en la base de datos
                    'notas_generales' => $request->notas_generales,
                    'alergias' => $request->alergias,
                    'antecedentes_medicos' => $request->antecedentes,
                    'fecha_nacimiento' => $request->fecha_nacimiento,
                    'whatsapp_id' => null,
                    'consentimiento' => null,
                    'ultima_interaccion' => null,
                ]);

                // Generamos el código del triage (TRI-AÑO-0001)
                $claveTriage = $this->generarCodigoConReinicioAnual(Triage::class, 'triage_codigo', 'TRI');

                $triage = Triage::create([
                    'triage_codigo' => $claveTriage,
                    'paciente_id' => $paciente->id,
                    'codigo_paciente' => $paciente->paciente_id,
                    'presion' => $request->presion_arterial,
                    'saturacion' => $request->saturacion,
                    'temperatura' => $request->temperatura,
                    'sintomas' => $request->sintomas,
                    'estado' => 'grave',
                    'nivel_urgencia' => null,
                    'evaluacion_ia' => null,
                    'requiere_medico' => 0,
                    'frecuencia_cardiaca' => $request->frecuencia_cardiaca,
                    'frecuencia_respiratoria' => $request->frecuencia_respiratoria,
                    'peso' => $request->peso,
                    'talla' => $request->talla,
                    'motivo_consulta' => $request->motivo_consulta,
                ]);

                return [$paciente, $triage];
            });

            [$paciente, $triage] = $resultado;

            return response()->json([
                'success' => true,
                'message' => 'Paciente y triage creados correctamente',
                'data' => [
                    'Paciente' => $paciente,
                    'Triage' => $triage,
                ]
            ]);
    

        } catch (\Exception $e) {
            \Log::error("Error en PacienteController@store: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'error'   => 'No se pudo registrar el paciente.'
            ], 500);
        }
    }

    /**
     * Genera un código con formato PREFIJO-AAAA-NNNN, donde NNNN es un
     * consecutivo que se reinicia en 0001 cada vez que cambia el año.
     *
     * Reutilizable para cualquier modelo/columna (paciente_id, triage_codigo,
     * folio de consulta, etc.) — solo cambia el modelo, la columna donde
     * vive el código y el prefijo.
     *
     * IMPORTANTE: debe llamarse dentro de un DB::transaction() (ver store())
     * junto con lockForUpdate, para que dos altas simultáneas no generen
     * el mismo número consecutivo.
     *
     * @param string $modelo   Clase del modelo, ej. Paciente::class
     * @param string $columna  Columna donde vive el código, ej. 'paciente_id'
     * @param string $prefijo  Prefijo del código, ej. 'PAC'
     */
    private function generarCodigoConReinicioAnual(string $modelo, string $columna, string $prefijo): string
    {
        $anioActual = date('Y');

        // Bloqueamos las filas de este año para que otra petición
        // concurrente no lea el mismo último número antes de que
        // esta transacción confirme su INSERT.
        $ultimoRegistro = $modelo::where($columna, 'LIKE', "{$prefijo}-{$anioActual}-%")
            ->orderBy('id', 'desc')
            ->lockForUpdate()
            ->first();

        if ($ultimoRegistro) {
            // Extraemos el consecutivo del último código
            // (ej. de "PAC-2026-0007" tomamos "0007")
            $ultimoNumero = (int) substr($ultimoRegistro->{$columna}, -4);
            $numero = $ultimoNumero + 1;
        } else {
            // Primer registro de este tipo en el año
            $numero = 1;
        }

        return $prefijo . '-' . $anioActual . '-' . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }

    public function show(string $id)
    {
       
        $paciente = Paciente::with([
            'triages',
            'archivos',
            'recetas.detalles',
            'recetas.consulta',
            'recetas.medico'
        ])->findOrFail($id);

        return response()->json($paciente);
    }

    public function edit(string $id)
    {
        $paciente = Paciente::findOrFail($id);
        return view('pacientes.edit', compact('paciente'));
    }

    /**
     * Actualiza los datos propios del paciente (no relaciones).
     *
     * IMPORTANTE:
     * - Se usa $request->except([...]) en vez de $request->all() para NO
     *   intentar guardar 'triages' (ni otros campos que no son columnas
     *   de la tabla `pacientes`) como si fueran atributos del modelo.
     *   Antes esto provocaba que Eloquent intentara hacer un UPDATE con
     *   una columna 'triages' inexistente, rompiendo la petición.
     * - Se responde con response()->json() en vez de redirect(), porque
     *   esta ruta es consumida por el frontend Vue vía axios/ApiService,
     *   que espera JSON. Un redirect() aquí regresaba un 302 hacia una
     *   vista HTML, lo cual el cliente HTTP no maneja como una actualización
     *   exitosa (y en algunos casos el navegador reintenta con GET,
     *   generando comportamientos como el 405 reportado).
     */
    public function update(Request $request, string $id)
    {
        $paciente = Paciente::findOrFail($id);

        // Excluimos relaciones y campos que no deben (o no pueden)
        // actualizarse por aquí. 'triages' se maneja por separado en
        // TriageController@guardarTriageRapido.
        $datos = $request->except(['triages', 'id', 'paciente_id']);

        $paciente->update($datos);

        // Regresamos el paciente actualizado junto con sus triages,
        // para que el frontend pueda refrescar el panel sin pegarle
        // de nuevo a /pacientes.
        return response()->json($paciente->fresh()->load('triages'));
    }

    public function destroy(string $id)
    {
        $paciente = Paciente::findOrFail($id);
        $paciente->delete();

        return back();
    }
}