<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\Consulta;
use App\Models\Receta;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(){
        return view('dashboard',[
            'pacientes' => Paciente::count(),
            'consultas' => Consulta::count(),
            'recetas' => Receta::count()
        ]);
    }

    // NUEVO: cifras del dashboard SPA (Home.vue) — consultas usadas,
    // finalizadas y pendientes el día de hoy, todas desde la tabla
    // `consultas` (misma fuente para las 3, así siempre cuadran entre sí:
    // usadas_hoy = finalizadas_hoy + pendientes_hoy).
    public function consultasHoy()
    {
        $hoy = now()->toDateString();

        $usadas = Consulta::whereDate('created_at', $hoy)->count();
        $finalizadas = Consulta::where('estado_consulta', 'finalizada')
                            ->whereDate('updated_at', $hoy)
                            ->count();

        return response()->json([
            'usadas_hoy'      => $usadas,
            'finalizadas_hoy' => $finalizadas,
            'pendientes_hoy'  => max(0, $usadas - $finalizadas),
        ]);
    }
}