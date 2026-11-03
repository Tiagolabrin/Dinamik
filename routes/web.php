<?php

use App\Models\Odontologo;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use RoundlyConsulting\Appointments\Models\Appointment;

Route::view('/', 'inicio')->name('inicio');

Route::view('/login', 'login')->name('login');
Route::view('/registro', 'registro')->name('registro');

Route::get('/perfil', function () {
    $user = Auth::user()
        ?? User::with([
            'paciente',
            'odontologo.disponibilidades',
            'odontologo.vacaciones',
        ])->first();

    abort_if(! $user, 404);

    return view('perfil', compact('user'));
})->name('perfil');

Route::get('/perfil/editar', function () {
    $user = Auth::user()
        ?? User::with([
            'paciente',
            'odontologo.disponibilidades',
            'odontologo.vacaciones',
        ])->first();

    abort_if(! $user, 404);

    return view('perfil_editar', compact('user'));
})->name('perfil_editar');

Route::get('/reserva', function () {
    $odontologos = Odontologo::with('user')->get();

    return view('turnos.reservar', compact('odontologos'));
})->name('reserva');

Route::get('/mis-turnos', function () {
    $appointments = Appointment::query()
        ->orderBy('starts_at')
        ->get();

    return view('turnos.mis_turnos', compact('appointments'));
})->name('mis_turnos');

Route::get('/agenda', function () {
    $appointments = Appointment::query()
        ->whereBetween('starts_at', [
            now()->startOfMonth(),
            now()->endOfMonth(),
        ])
        ->orderBy('starts_at')
        ->get();

    return view('agenda', compact('appointments'));
})->name('agenda');

Route::get('/odontologo/agenda', function () {
    $appointments = Appointment::query()
        ->orderBy('starts_at')
        ->get();

    return view('odontologo.agenda', compact('appointments'));
})->name('odontologo.agenda');

Route::get('/turno/confirmado/{appointment}', function (Appointment $appointment) {
    $consultorio = \App\Models\Consultorio::first();

    return view('turnos.confirmacion', compact('appointment', 'consultorio'));
})->name('turno.confirmado');
