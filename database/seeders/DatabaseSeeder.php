<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesPermisosSeeder::class,       // 1. Roles y permisos
            PipelineComercialSeeder::class,   // 2. Etapas del pipeline
            MaestrosComercialDemoSeeder::class, // 3. Catálogos (motivos, tipos, fuentes)
            UsuariosDemoSeeder::class,        // 4. Usuarios demo
        ]);
    }
}
