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
}
