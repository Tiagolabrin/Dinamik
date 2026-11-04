<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class OdontologoController extends Controller
{
    public function create(): View
    {
        return view('admin.odontologos.registro');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'matricula' => ['required', 'string', 'max:255', 'unique:odontologos,matricula'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        DB::transaction(function () use ($data): void {
            $user = User::create([
                'name' => $data['name'],
                'apellido' => $data['apellido'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'tipo_usuario' => 'odontologo',
            ]);

            $user->odontologo()->create([
                'matricula' => $data['matricula'],
            ]);
        });

        return redirect()
            ->route('admin.odontologos.registro')
            ->with('success', 'Cuenta de odontólogo creada correctamente.');
    }
}
