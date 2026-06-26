<?php

namespace App\Http\Controllers;

use App\Models\Medico;
use App\Models\HorarioMedico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MedicoController extends Controller
{
    public function store(Request $request)
    {
        // A. VALIDACIÓN DE LOS DATOS
        $request->validate([
            'nombre'             => 'required|string|max:255',
            'cedula_profesional' => 'nullable|string|max:50',
            // Verifica que el ID seleccionado exista en tu tabla `especialidades` y esté 'Activo'
            'especialidad'       => 'required|exists:especialidades,id,estado,Activo',
            'hora_entrada'       => 'required',
            'hora_salida'        => 'required',
            'dias_laborales'     => 'required|array|min:1',
        ]);

        // Mapeo para traducir el texto del Blade (Lunes, Martes...) al tinyint (1, 2...) de tu tabla `horarios_medicos`
        $mapeoDias = [
            'Lunes'     => 1,
            'Martes'    => 2,
            'Miércoles' => 3,
            'Jueves'    => 4,
            'Viernes'   => 5,
            'Sábado'    => 6,
            'Domingo'   => 7
        ];

        // B. INICIAR TRANSACCIÓN
        DB::beginTransaction();

        try {
            // 1. Autogenerar un folio único para cumplir con el NOT NULL de tu tabla `medicos`
            $folioMedico = 'MEDI-' . strtoupper(substr(uniqid(), -8));

            // 2. Insertar el registro principal en la tabla `medicos`
            $medico = Medico::create([
                'folio'              => $folioMedico,
                'nombre'             => $request->nombre,
                'cedula_profesional' => $request->cedula_profesional,
                'especialidad_id'    => $request->especialidad, // Aquí guardamos la llave foránea
                'activo'             => 1, // 1 = Activo por defecto
            ]);

            // 3. Iterar los días que el usuario seleccionó y guardarlos en `horarios_medicos`
            foreach ($request->dias_laborales as $diaNombre) {
                // Buscamos el número equivalente (si es Lunes pone 1, etc.)
                $diaNumero = $mapeoDias[$diaNombre] ?? 1;

                HorarioMedico::create([
                    'medico_id'         => $medico->id, // El ID autoincrementable que se acaba de generar arriba
                    'dia_semana'        => $diaNumero,
                    'hora_inicio'       => $request->hora_entrada,
                    'hora_fin'          => $request->hora_salida,
                    'duracion_consulta' => 30, // 30 minutos por defecto como pide tu estructura
                ]);
            }

            // C. CONFIRMAR CAMBIOS: Si todo se ejecutó sin errores, se guardan los datos en la BD permanentemente
            DB::commit();

            return redirect()->back()->with('success', '¡Médico y horarios registrados con éxito! Folio: ' . $folioMedico);

        } catch (\Exception $e) {
            // D. REVERTIR CAMBIOS: Si algo falla (ej. error de sintaxis), se deshace todo en ambas tablas
            DB::rollBack();

            return redirect()->back()
                ->withInput() // Mantiene lo que el usuario escribió para que no lo vuelva a digitar
                ->with('error', 'Error al guardar el registro: ' . $e->getMessage());
        }
    }
}