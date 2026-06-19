<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class PerfilController extends Controller
{
    public function obtenerPerfil()
    {
        dd(Auth::check());
    }
}