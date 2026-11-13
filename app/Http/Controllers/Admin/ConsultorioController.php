<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consultorio;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ConsultorioController extends Controller
{
    public function edit(): View
    {
        $consultorio = Consultorio::first();

        return view('admin.consultorio.editar', compact('consultorio'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'direccion' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        $consultorio = Consultorio::first();

        if ($consultorio) {
            $consultorio->update($data);
        } else {
            Consultorio::create($data);
        }

        return redirect()
            ->route('admin.consultorio.edit')
            ->with('success', 'Datos del consultorio guardados correctamente.');
    }
}
