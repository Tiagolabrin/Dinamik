<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PerfilController extends Controller
{
    public function update(Request $request): RedirectResponse {
        $user = $request->user();

        if (! $user instanceof User){
            abort(403);
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ];

        if($user->paciente){
            $rules = array_merge($rules, [
                'obra_social' => ['required', 'string', 'max:255'],
                'fecha_nacimiento' => ['required', 'date', 'before:today'],
                'direccion' => ['required', 'string', 'max:255'],
            ]);
        }elseif ($user->odontologo){
            $rules['matricula'] = ['required', 'string', 'max:255', Rule::unique('odontologos','matricula')->ignore($user->odontologo->id)];
            $rules = array_merge($rules, [
                'disponibilidades' => ['required', 'array', 'min:1'],
                'disponibilidades.*' => ['required', 'array:dia_semana,hora_inicio,hora_fin'],
                'disponibilidades.*.dia_semana' => ['required', Rule::in(['lunes','martes','miercoles','jueves','viernes'])],
                'disponibilidades.*.hora_inicio' =>['required','date_format:H:i'],
                'disponibilidades.*.hora_fin' => ['required', 'date_format:H:i', 'after:disponibilidades.*.hora_inicio'],
                'vacaciones' => ['sometimes', 'array'],
                'vacaciones.*' => ['required', 'array:fecha_desde,fecha_hasta'],
                'vacaciones.*.fecha_desde' => ['nullable', 'date_format:Y-m-d', 'required_with:vacaciones.*.fecha_hasta'],
                'vacaciones.*.fecha_hasta' => [ 'nullable', 'date_format:Y-m-d','required_with:vacaciones.*.fecha_desde','after_or_equal:vacaciones.*.fecha_desde']
            ]);
        }

        $data = $request->validate($rules);

        DB::transaction(function () use ($user, $data): void {
            $user->name = $data['name'];
            $user->apellido = $data['apellido'];
            $user->email = $data['email'];
            $user->save();

            if ($user->paciente) {
                $user->paciente->update([
                    'obra_social' => $data['obra_social'],
                    'fecha_nacimiento' => $data['fecha_nacimiento'],
                    'direccion' => $data['direccion'],
                ]);
            } elseif ($user->odontologo) {
                $odontologo = $user->odontologo;
                $odontologo->matricula = $data['matricula'];
                $odontologo->save();

                $odontologo->disponibilidades()->delete();

                foreach ($data['disponibilidades'] as $disponibilidad) {
                    $odontologo->disponibilidades()->create([
                        'dia_semana' => $disponibilidad['dia_semana'],
                        'hora_inicio' => $disponibilidad['hora_inicio'],
                        'hora_fin' => $disponibilidad['hora_fin'],
                    ]);
                }

                $odontologo->vacaciones()->delete();

                foreach ($data['vacaciones'] ?? [] as $vacacion) {
                    if (blank($vacacion['fecha_desde']) && blank($vacacion['fecha_hasta'])) {
                        continue;
                    }

                    $odontologo->vacaciones()->create([
                        'fecha_desde' => $vacacion['fecha_desde'],
                        'fecha_hasta' => $vacacion['fecha_hasta'],
                    ]);
                }
            }
        });

        return redirect()
            ->route('perfil')
            ->with('success', 'Perfil actualizado correctamente.');
    }

    public function show(): View{
        $user = User::with([
            'paciente',
            'odontologo.disponibilidades',
            'odontologo.vacaciones',
        ])->findOrFail(Auth::id());

        return view('perfil', compact('user'));
    }

    public function edit(): View {
        $user = User::with([
            'paciente',
            'odontologo.disponibilidades',
            'odontologo.vacaciones',
        ])->findOrFail(Auth::id());

        return view('perfil_editar', compact('user'));
    }
}
