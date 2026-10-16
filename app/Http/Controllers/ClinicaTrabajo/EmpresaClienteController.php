<?php

namespace App\Http\Controllers\ClinicaTrabajo;

use App\Http\Controllers\Controller;
use App\Models\ClinicaTrabajo\EmpresaCliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class EmpresaClienteController extends Controller
{
    public function index(Request $request)
    {
        // Lista compacta para los selects de los formularios
        if ($request->boolean('select')) {
            $empresas = EmpresaCliente::where('activo', 1)
                ->orderBy('razon_social')
                ->get(['id', 'razon_social', 'giro_actividad']);

            return response()->json($empresas);
        }

        $query = EmpresaCliente::query();

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('razon_social', 'like', "%{$buscar}%")
                  ->orWhere('rfc', 'like', "%{$buscar}%")
                  ->orWhere('folio', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        $empresas = $query->orderBy('razon_social')->paginate(15);

        return response()->json($empresas);
    }

    // Pendiente de definir: JSON o vista Blade
    public function create()
    {
        return response()->json(['message' => 'Pendiente de definir.'], 501);
    }

    public function store(Request $request)
    {
        $request->validate([
            'razon_social'      => 'required|string|max:255',
            'rfc'               => 'nullable|string|max:13|unique:empresas_cliente,rfc',
            'giro_actividad'    => 'nullable|string|max:255',
            'direccion'         => 'nullable|string|max:500',
            'contacto_nombre'   => 'nullable|string|max:255',
            'contacto_telefono' => 'nullable|string|max:20',
            'contacto_email'    => 'nullable|email|max:255',
            'activo'            => 'sometimes|boolean',
            'puestos'           => 'nullable|array',
            'puestos.*.id'      => 'nullable|integer',
            'puestos.*.nombre'  => 'required|string|max:255',
        ]);

        try {
            $empresa = EmpresaCliente::create([
                'razon_social'      => $request->razon_social,
                'rfc'               => $request->rfc,
                'giro_actividad'    => $request->giro_actividad,
                'direccion'         => $request->direccion,
                'contacto_nombre'   => $request->contacto_nombre,
                'contacto_telefono' => $request->contacto_telefono,
                'contacto_email'    => $request->contacto_email,
                'activo'            => $request->boolean('activo', true) ? 1 : 0,
            ]);

            $this->sincronizarPuestos($empresa, $request);

            return response()->json([
                'success' => true,
                'message' => 'Empresa registrada correctamente',
                'folio'   => $empresa->folio,
                'empresa' => $empresa,
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error al registrar empresa cliente: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la empresa',
            ], 500);
        }
    }

    /**
     * Crea, renombra y da de baja puestos de la empresa.
     * Solo actúa si el request trae la llave "puestos".
     * Los puestos que ya no vienen se desactivan, no se borran,
     * porque pueden tener asignaciones de pacientes.
     */
    private function sincronizarPuestos(EmpresaCliente $empresa, Request $request): void
    {
        if (!$request->has('puestos')) {
            return;
        }

        $idsConservados = [];

        foreach ($request->input('puestos', []) as $p) {
            if (!empty($p['id'])) {
                $puesto = $empresa->puestosTrabajo()->find($p['id']);
                if ($puesto) {
                    $puesto->update(['nombre' => $p['nombre'], 'activo' => 1]);
                    $idsConservados[] = $puesto->id;
                }
            } else {
                $idsConservados[] = $empresa->puestosTrabajo()
                    ->create(['nombre' => $p['nombre'], 'activo' => 1])
                    ->id;
            }
        }

        $empresa->puestosTrabajo()
            ->where('activo', 1)
            ->whereNotIn('id', $idsConservados)
            ->update(['activo' => 0]);
    }

    public function show($id)
    {
        $empresa = EmpresaCliente::with([
            'puestosTrabajo' => fn ($q) => $q->where('activo', 1)->orderBy('nombre'),
        ])->findOrFail($id);

        return response()->json($empresa);
    }

    // Pendiente de definir: JSON o vista Blade
    public function edit($id)
    {
        return response()->json(['message' => 'Pendiente de definir.'], 501);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'razon_social'      => 'required|string|max:255',
            'rfc'               => ['nullable', 'string', 'max:13', Rule::unique('empresas_cliente', 'rfc')->ignore($id)],
            'giro_actividad'    => 'nullable|string|max:255',
            'direccion'         => 'nullable|string|max:500',
            'contacto_nombre'   => 'nullable|string|max:255',
            'contacto_telefono' => 'nullable|string|max:20',
            'contacto_email'    => 'nullable|email|max:255',
            'activo'            => 'sometimes|boolean',
            'puestos'           => 'nullable|array',
            'puestos.*.id'      => 'nullable|integer',
            'puestos.*.nombre'  => 'required|string|max:255',
        ]);

        try {
            $empresa = EmpresaCliente::findOrFail($id);

            $empresa->razon_social      = $request->razon_social;
            $empresa->rfc               = $request->rfc;
            $empresa->giro_actividad    = $request->giro_actividad;
            $empresa->direccion         = $request->direccion;
            $empresa->contacto_nombre   = $request->contacto_nombre;
            $empresa->contacto_telefono = $request->contacto_telefono;
            $empresa->contacto_email    = $request->contacto_email;

            if ($request->has('activo')) {
                $empresa->activo = $request->boolean('activo') ? 1 : 0;
            }

            $empresa->save();
            $this->sincronizarPuestos($empresa, $request);

            return response()->json([
                'success' => true,
                'message' => 'Empresa actualizada correctamente',
                'folio'   => $empresa->folio,
                'empresa' => $empresa->fresh(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar empresa cliente: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la empresa',
            ], 500);
        }
    }

    // Baja lógica: la empresa se conserva porque puede tener datos laborales asociados
    public function destroy($id)
    {
        $empresa = EmpresaCliente::find($id);

        if (!$empresa) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Empresa no encontrada',
            ], 404);
        }

        $empresa->activo = 0;
        $empresa->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Empresa dada de baja correctamente',
        ]);
    }
}