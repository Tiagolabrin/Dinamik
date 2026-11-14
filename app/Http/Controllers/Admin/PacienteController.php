<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PacienteController extends Controller
{
    public function index(): View
    {
        $pacientes = Paciente::with('user')->get();

        return view('admin.pacientes.index', compact('pacientes'));
    }

    public function edit(Paciente $paciente): View
    {
        $paciente->load('user');

        return view('admin.pacientes.editar', compact('paciente'));
    }

    public function update(Request $request, Paciente $paciente): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'obra_social' => ['required', 'string', 'max:255'],
            'fecha_nacimiento' => ['required', 'date'],
            'direccion' => ['required', 'string', 'max:255'],
        ]);

        $paciente->user->update([
            'name' => $data['name'],
            'apellido' => $data['apellido'],
        ]);

        $paciente->update([
            'obra_social' => $data['obra_social'],
            'fecha_nacimiento' => $data['fecha_nacimiento'],
            'direccion' => $data['direccion'],
        ]);

        return redirect()
            ->route('admin.pacientes.index')
            ->with('success', 'Datos del paciente actualizados correctamente.');
    }
}