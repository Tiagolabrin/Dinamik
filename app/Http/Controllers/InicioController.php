<?php

namespace App\Http\Controllers;

use App\Models\Consultorio;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class InicioController extends Controller
{
    public function index(): View{
        $consultorio = Consultorio::first();
    
        return view('inicio', compact('consultorio'));
    }
}
