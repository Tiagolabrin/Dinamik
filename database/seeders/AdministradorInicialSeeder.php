<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdministradorInicialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nombre = config('app.admin_initial.name');
        $email = config('app.admin_initial.email');
        $password = config('app.admin_initial.password');

        if (! $nombre || ! $email || ! $password) {
            throw new \RuntimeException('Faltan las credenciales iniciales del administrador en el archivo .env.');
        }

        $usuario = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $nombre,
                'tipo_usuario' => 'administrador',
                'password' => Hash::make($password),
            ]
        );

        if ($usuario->tipo_usuario !== 'administrador') {
            throw new \RuntimeException('El email configurado ya pertenece a un usuario que no es administrador.');
        }
    }
}
