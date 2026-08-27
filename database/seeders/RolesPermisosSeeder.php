<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesPermisosSeeder extends Seeder
{
    public function run(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        $permisos = [
            // Clientes
            'clientes.ver',
            'clientes.crear',
            'clientes.editar',
            'clientes.eliminar',
            'clientes.restaurar',
            // Contactos
            'contactos.ver',
            'contactos.crear',
            'contactos.editar',
            'contactos.eliminar',
            // Seguimientos
            'seguimientos.ver',
            'seguimientos.crear',
            'seguimientos.editar',
            'seguimientos.eliminar',
            // Dashboard
            'dashboard.ver',
            'dashboard.kpis',
            // Reportes
            'reportes.ver',
            'reportes.exportar',
            // ERP
            'erp.consultar',
            // Prospectos
            'prospectos.ver',
            'prospectos.crear',
            'prospectos.editar',
            'prospectos.eliminar',
            'prospectos.convertir',
            // Negocios
            'negocios.ver',
            'negocios.crear',
            'negocios.editar',
            'negocios.eliminar',
            'negocios.cerrar',
            // Solicitudes de crédito
            'solicitudes_credito.ver',
            'solicitudes_credito.crear',
            'solicitudes_credito.eliminar',
            'solicitudes_credito.revisar',
            // Contabilidad
            'clientes.revisar_contabilidad',
            // Pipeline
            'pipeline.ver',
            'pipeline.mover',
            // Forecast
            'forecast.ver',
            // Maestros comerciales
            'maestros.ver',
            'maestros.gestionar',
            // Admin
            'usuarios.gestionar',
            'roles.gestionar',
            // Producción
            'existencias_mp.ver',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        $gerente = Role::firstOrCreate(['name' => 'gerente', 'guard_name' => 'web']);
        $gerente->syncPermissions([
            'clientes.ver',
            'contactos.ver',
            'seguimientos.ver',
            'prospectos.ver',
            'negocios.ver',
            'solicitudes_credito.ver',
            'pipeline.ver',
            'forecast.ver',
            'dashboard.ver', 'dashboard.kpis',
            'reportes.ver', 'reportes.exportar',
            'erp.consultar',
        ]);

        $comercial = Role::firstOrCreate(['name' => 'comercial', 'guard_name' => 'web']);
        $comercial->syncPermissions([
            'clientes.ver', 'clientes.crear', 'clientes.editar',
            'contactos.ver', 'contactos.crear', 'contactos.editar',
            'seguimientos.ver', 'seguimientos.crear', 'seguimientos.editar',
            'prospectos.ver', 'prospectos.crear', 'prospectos.editar', 'prospectos.convertir',
            'negocios.ver', 'negocios.crear', 'negocios.editar',
            'solicitudes_credito.ver', 'solicitudes_credito.crear',
            'pipeline.ver', 'pipeline.mover',
            'forecast.ver',
            'maestros.ver',
            'dashboard.ver',
            'erp.consultar',
        ]);

        $cartera = Role::firstOrCreate(['name' => 'cartera', 'guard_name' => 'web']);
        $cartera->syncPermissions([
            'solicitudes_credito.ver', 'solicitudes_credito.revisar',
        ]);

        $contabilidadFormacol = Role::firstOrCreate(['name' => 'contabilidad_formacol', 'guard_name' => 'web']);
        $contabilidadFormacol->syncPermissions([
            'clientes.ver',
            'clientes.revisar_contabilidad',
        ]);

        $contabilidadContiflex = Role::firstOrCreate(['name' => 'contabilidad_contiflex', 'guard_name' => 'web']);
        $contabilidadContiflex->syncPermissions([
            'clientes.ver',
            'clientes.revisar_contabilidad',
        ]);

        $produccion = Role::firstOrCreate(['name' => 'produccion', 'guard_name' => 'web']);
        $produccion->syncPermissions([
            'existencias_mp.ver',
        ]);
    }
}
