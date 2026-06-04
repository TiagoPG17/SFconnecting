<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuariosDemoSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
            [
                'name'     => 'Administrador',
                'email'    => 'admin@sfconnecting.co',
                'password' => Hash::make('Admin2026*'),
                'rol'      => 'admin',
            ],
            [
                'name'     => 'Laura Gerente',
                'email'    => 'gerente@sfconnecting.co',
                'password' => Hash::make('Gerente2026*'),
                'rol'      => 'gerente',
            ],
            [
                'name'     => 'Carlos Asesor',
                'email'    => 'asesor@sfconnecting.co',
                'password' => Hash::make('Asesor2026*'),
                'rol'      => 'comercial',
            ],
        ];

        foreach ($usuarios as $data) {
            $rol = $data['rol'];
            unset($data['rol']);

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                $data
            );

            if (! $user->hasRole($rol)) {
                $user->assignRole($rol);
            }
        }
    }
}
