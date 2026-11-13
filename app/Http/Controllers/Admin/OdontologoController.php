<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Models\Odontologo;

class OdontologoController extends Controller
{
    public function index(): View
    {
        $odontologos = Odontologo::with('user')->get();

        return view('admin.odontologos.index', compact('odontologos'));
    }
    public function create(): View
    {
        return view('admin.odontologos.registro');
    }
    public function edit(Odontologo $odontologo): View
    {
        $odontologo->load('user');

        return view('admin.odontologos.editar', compact('odontologo'));
    }
    public function update(Request $request, Odontologo $odontologo): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $odontologo->user_id],
            'matricula' => ['required', 'string', 'max:255', 'unique:odontologos,matricula,' . $odontologo->id],
        ]);

        DB::transaction(function () use ($data, $odontologo): void {
            $odontologo->user->update([
                'name' => $data['name'],
                'apellido' => $data['apellido'],
                'email' => $data['email'],
            ]);

            $odontologo->update([
                'matricula' => $data['matricula'],
            ]);
        });

        return redirect()
            ->route('admin.odontologos.index')
            ->with('success', 'Datos del odontólogo actualizados correctamente.');
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
            ->route('admin.odontologos.index')
            ->with('success', 'Cuenta de odontólogo creada correctamente.');
    }
}
