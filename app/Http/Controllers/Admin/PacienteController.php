<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function destroy(Paciente $paciente): RedirectResponse
    {
        $paciente->load('user');

        $turnosFuturos = \RoundlyConsulting\Appointments\Models\Appointment::whereHas(
            'participants',
            function ($query) use ($paciente) {
                $query->where('participant_type', \App\Models\User::class)
                    ->where('participant_id', $paciente->user_id);
            }
        )
            ->where('starts_at', '>=', now()->startOfDay())
            ->exists();

        if ($turnosFuturos) {
            return redirect()
                ->route('admin.pacientes.index')
                ->with('error', 'No se puede eliminar este paciente porque tiene turnos agendados.');
        }

        DB::transaction(function () use ($paciente) {
            $paciente->user->delete();
        });

        return redirect()
            ->route('admin.pacientes.index')
            ->with('success', 'Paciente eliminado correctamente.');
    }
}
