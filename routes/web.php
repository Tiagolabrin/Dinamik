
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\Admin\OdontologoController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\ReservaController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ConsultorioController;

Route::get('/', [InicioController::class, 'index'])
    ->name('inicio');

Route::view('/login', 'login')->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.submit');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');

Route::view('/registro', 'registro')
    ->name('registro');

Route::post('/registro', [RegistroController::class, 'store'])
    ->name('registro.store');

Route::get('/perfil', [PerfilController::class, 'show'])
    ->middleware('auth')
    ->name('perfil');

Route::get('/perfil/editar', [PerfilController::class, 'edit'])
    ->middleware('auth')
    ->name('perfil_editar');

Route::post('/perfil/editar', [PerfilController::class, 'update'])
    ->middleware('auth')
    ->name('perfil.update');

Route::get('/reserva', [ReservaController::class, 'sacarTurno'])
    ->name('reserva');

Route::post('/reserva', [ReservaController::class, 'reservar'])
    ->middleware('auth')
    ->name('reserva.store');

Route::get('/reserva/horarios', [ReservaController::class, 'horarios'])
    ->name('reserva.horarios');

Route::get('/mis-turnos', [ReservaController::class, 'misTurnos'])
    ->middleware('auth')
    ->name('mis_turnos');


Route::get('/odontologo/agenda', [ReservaController::class, 'agenda'])
    ->middleware('auth')
    ->name('odontologo.agenda');

Route::get('/turno/confirmado/{appointment}', [ReservaController::class, 'confirmacion'])
    ->middleware('auth')
    ->name('turno.confirmado');

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/odontologos', [OdontologoController::class, 'index'])
            ->name('odontologos.index');

        Route::get('/odontologos/crear', [OdontologoController::class, 'create'])
            ->name('odontologos.registro');

        Route::post('/odontologos', [OdontologoController::class, 'store'])
            ->name('odontologos.store');
        Route::get('/odontologos/{odontologo}/editar', [OdontologoController::class, 'edit'])
            ->name('odontologos.edit');

        Route::put('/odontologos/{odontologo}', [OdontologoController::class, 'update'])
            ->name('odontologos.update');
            
        Route::get('/consultorio', [ConsultorioController::class, 'edit'])
            ->name('consultorio.edit');

        Route::put('/consultorio', [ConsultorioController::class, 'update'])
            ->name('consultorio.update');
    });
