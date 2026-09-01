<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Especialidad;
use App\Models\HorarioMedico;
use App\Models\Medico;
use App\Models\Ubicacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OnboardingController extends Controller
{
    public function index()
    {
        return view('onboarding.index');
    }

    /**
     * Recibe TODO lo capturado en los 4 pasos del wizard en un solo POST
     * (el frontend solo acumula los datos en memoria mientras navega los
     * pasos; el guardado real ocurre aquí, al final).
     */
    public function completar(Request $request)
    {
        $validated = $request->validate([
            // Paso 1: Empresa
            'empresa.nombre_empresa' => ['required', 'string', 'max:255'],
            'empresa.razon_social' => ['required', 'string', 'max:255'],
            'empresa.rfc' => ['required', 'string', 'max:20', 'unique:configuracion_empresa,rfc'],
            'empresa.telefono' => ['nullable', 'string', 'max:20'],
            'empresa.email' => ['nullable', 'email', 'max:255'],
            'empresa.direccion' => ['required', 'string', 'max:255'],
            'empresa.logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            // Paso 2: Ubicación
            'ubicacion.folio_sucursal' => ['nullable', 'string', 'max:50'],
            'ubicacion.nombre' => ['required', 'string', 'max:255'],
            'ubicacion.direccion' => ['required', 'string', 'max:255'],
            'ubicacion.telefono' => ['nullable', 'string', 'max:20'],
            'ubicacion.horario_apertura' => ['required'],
            'ubicacion.horario_cierre' => ['required'],
            // Antes: 'ubicacion.imagen' => 'nullable|string' (no coincidía
            // con el nombre real del campo del formulario y tampoco
            // aceptaba un archivo). Ahora sí se valida el archivo real
            // que manda el wizard como 'ubicacion[logo]'.
            'ubicacion.logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            // Paso 3: Médico (sin login propio)
            'medico.nombre' => ['required', 'string', 'max:255'],
            'medico.cedula_profesional' => ['required', 'string', 'max:50', 'unique:medicos,cedula_profesional'],
            'medico.costo_consulta' => ['required', 'numeric', 'min:0'],
            'medico.folio' => ['nullable', 'string', 'max:50'],
            'medico.horarios' => ['required', 'array', 'min:1'],
            'medico.horarios.*.dia_semana' => ['required', 'integer', 'between:1,7'],
            'medico.horarios.*.hora_inicio' => ['required', 'date_format:H:i'],
            'medico.horarios.*.hora_fin' => ['required', 'date_format:H:i', 'after:medico.horarios.*.hora_inicio'],
            'medico.horarios.*.duracion_consulta' => ['required', 'integer', 'min:5'],

            // Paso 4: Especialidad
            'especialidad.name' => ['required', 'string', 'max:100'],
            'especialidad.description' => ['nullable', 'string'],
            
            // Paso 5: Correo (Opcionales por si el usuario decide omitirlo)
            'correo.tipo' => ['nullable', 'string', 'max:50'],
            'correo.mail_host' => ['nullable', 'string', 'max:255'],
            'correo.mail_port' => ['nullable', 'integer'],
            'correo.mail_username' => ['nullable', 'string', 'max:255'],
            'correo.mail_password' => ['nullable', 'string', 'max:255'],
            'correo.mail_encryption' => ['nullable', 'string', 'max:20'],

        ], [
            'medico.horarios.*.hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
        ]);

        // Evita días duplicados en el arreglo de horarios
        $dias = array_column($validated['medico']['horarios'], 'dia_semana');
        if (count($dias) !== count(array_unique($dias))) {
            return response()->json(['message' => 'No puedes repetir el mismo día en el horario.'], 422);
        }

        DB::beginTransaction();

        try {
            // 1. Logo de la empresa (si se subió). Mismo patrón que el
            // logo de la sucursal: nombre único basado en el nombre +
            // año + uniqid, guardado en public/personalisarperfil.
            $rutaLogoEmpresa = null;
            if ($request->hasFile('empresa.logo')) {
                $rutaLogoEmpresa = $this->guardarImagenLogo(
                    $request->file('empresa.logo'),
                    $validated['empresa']['nombre_empresa']
                );
            }
            
            // Datos de correo opcionales enviados desde el paso 5
            $datosCorreo = $validated['correo'] ?? [];


            // 2. Empresa. Va primero porque la ubicación se liga a ella
            // mediante empresa_id.
            $empresa = Empresa::create([
                'nombre_empresa' => $validated['empresa']['nombre_empresa'],
                'razon_social' => $validated['empresa']['razon_social'],
                'rfc' => $validated['empresa']['rfc'],
                'logo_url' => $rutaLogoEmpresa,
                'telefono' => $validated['empresa']['telefono'] ?? null,
                'email' => $validated['empresa']['email'] ?? null,
                'direccion' => $validated['empresa']['direccion'],
                // Configuración de correo (si los omitió, se quedan en null o vacíos en la fila)
                'mail_host' => $datosCorreo['mail_host'] ?? null,
                'mail_port' => $datosCorreo['mail_port'] ?? null,
                'mail_username' => $datosCorreo['mail_username'] ?? null,
                'mail_password' => !empty($datosCorreo['mail_password']) ? encrypt($datosCorreo['mail_password']) : null,
                'mail_encryption' => $datosCorreo['mail_encryption'] ?? null,

            ]);

            // 3. Especialidad: el médico la necesita para su especialidad_id.
            // Usa el modelo real Especialidad (tabla `especialidades`), con las
            // columnas reales nombre/descripcion, y genera el folio requerido
            // por la tabla (no lo manda el frontend).
            $especialidad = Especialidad::create([
                'folio' => $this->generarFolio('ESP', Especialidad::class),
                'nombre' => $validated['especialidad']['name'],
                'descripcion' => $validated['especialidad']['description'] ?? null,
                'estado' => 'Activo',
            ]);

            // 4. Logo de la ubicación (si se subió). Mismo patrón que
            // Ubicacion::guardarImagenLogo(): nombre único basado en el
            // nombre de la sede + año + uniqid, guardado en
            // public/personalisarperfil.
            $rutaLogo = null;
            if ($request->hasFile('ubicacion.logo')) {
                $rutaLogo = $this->guardarImagenLogo(
                    $request->file('ubicacion.logo'),
                    $validated['ubicacion']['nombre']
                );
            }

            // 5. Ubicación. folio_sucursal es NOT NULL en la tabla, así que
            // se genera aquí igual que el de especialidad (el wizard no lo
            // manda, a diferencia del formulario normal de sucursales que
            // sí lo arma en el frontend). Se liga a la empresa recién
            // creada mediante empresa_id.
            $ubicacion = Ubicacion::create([
                //'empresa_id' => $empresa->id,
                'folio_sucursal' => $validated['ubicacion']['folio_sucursal'] ?? $this->generarFolio('UBIC', Ubicacion::class),
                'nombre' => $validated['ubicacion']['nombre'],
                'direccion' => $validated['ubicacion']['direccion'],
                'imagen' => $rutaLogo,
                'telefono' => $validated['ubicacion']['telefono'] ?? null,
                'activo' => true,
                'horario_apertura' => $validated['ubicacion']['horario_apertura'],
                'horario_cierre' => $validated['ubicacion']['horario_cierre'],
            ]);

            // 6. Médico — sin user_id porque este flujo no le crea login.
            // OJO: si la columna user_id en `medicos` es NOT NULL, esto
            // truena. En ese caso avísame y la hacemos nullable con una
            // migración, o asignamos aquí el user_id del propio admin.
            $medico = Medico::create([
                'user_id' => null,
                'folio' => $validated['medico']['folio'] ?? $this->generarFolio('MED', Medico::class),
                'nombre' => $validated['medico']['nombre'],
                'cedula_profesional' => $validated['medico']['cedula_profesional'],
                'especialidad_id' => $especialidad->id,
                'costo_consulta' => $validated['medico']['costo_consulta'],
                'activo' => true,
            ]);

            // 7. Horarios del médico, todos ligados a la ubicación recién creada
            foreach ($validated['medico']['horarios'] as $horario) {
                HorarioMedico::create([
                    'medico_id' => $medico->id,
                    'dia_semana' => $horario['dia_semana'],
                    'hora_inicio' => $horario['hora_inicio'],
                    'hora_fin' => $horario['hora_fin'],
                    'duracion_consulta' => $horario['duracion_consulta'],
                    'ubicacion_id' => $ubicacion->id,
                ]);
            }

            // 8. Marca el onboarding del admin como completado
            $request->user()->update(['onboarding_completado' => true]);

            DB::commit();

            return response()->json([
                'message' => 'Configuración inicial completada correctamente.',
                'redirect' => route('dashboard'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Ocurrió un error al guardar la información: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Guarda el logo (de empresa o de sede) en public/personalisarperfil
     * con un nombre único (mismo patrón usado en UbicacionController).
     */
    private function guardarImagenLogo($archivo, string $nombreBase): string
    {
        $slug = Str::slug($nombreBase);
        $nombreArchivo = $slug . '-' . date('Y') . '-' . uniqid() . '.' . $archivo->getClientOriginalExtension();

        $archivo->move(public_path('personalisarperfil'), $nombreArchivo);

        return 'personalisarperfil/' . $nombreArchivo;
    }

    /**
     * Genera un folio único con patrón PREFIJO-AÑO-CORRELATIVO(4 dígitos),
     * mismo estilo que folio_sucursal (ej. ESP-2026-0001). No depende de
     * created_at porque algunos modelos (p.ej. Ubicacion) no usan timestamps.
     */
    private function generarFolio(string $prefijo, string $modelo): string
    {
        $anio = date('Y');
        $correlativo = $modelo::count() + 1;

        return sprintf('%s-%s-%04d', $prefijo, $anio, $correlativo);
    }

    public function actualizarCorreo(Request $request)
    {
        $validated = $request->validate([
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_encryption' => ['nullable', 'string', 'max:20'],
        ]);

        // Buscamos la empresa existente (la única fila de configuración)
        $empresa = Empresa::first(); 

        if (!$empresa) {
            return response()->json(['message' => 'No se encontró la empresa.'], 404);
        }

        // Actualizamos únicamente los campos de correo que llegaron (o rellenamos los vacíos)
        $datosAActualizar = [
            'mail_host' => $validated['mail_host'] ?? $empresa->mail_host,
            'mail_port' => $validated['mail_port'] ?? $empresa->mail_port,
            'mail_username' => $validated['mail_username'] ?? $empresa->mail_username,
            'mail_encryption' => $validated['mail_encryption'] ?? $empresa->mail_encryption,
        ];

        if (!empty($validated['mail_password'])) {
            $datosAActualizar['mail_password'] = encrypt($validated['mail_password']);
        }

        // Aquí ocurre la actualización de la fila existente
        $empresa->update($datosAActualizar);

        return response()->json([
            'success' => true,
            'message' => 'Configuración de correo actualizada correctamente.'
        ]);
    }
}