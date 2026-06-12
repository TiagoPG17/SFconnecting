<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\ContactoController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ERPController;
use App\Http\Controllers\Api\MaestroController;
use App\Http\Controllers\Api\NegocioController;
use App\Http\Controllers\Api\ProspectoController;
use App\Http\Controllers\Api\ReporteController;
use App\Http\Controllers\Api\SeguimientoController;
use App\Http\Controllers\Api\UsuarioController;
use Illuminate\Support\Facades\Route;

// Auth — público (5 intentos por minuto por IP)
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('auth.login');
});

Route::middleware('auth:sanctum')->name('api.')->group(function () {

    // Auth — protegido
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('me', [AuthController::class, 'me'])->name('auth.me');
    });

    // Dashboard
    Route::get('dashboard/kpis', [DashboardController::class, 'kpis'])->name('dashboard.kpis');

    // Reportes
    Route::prefix('reportes')->group(function () {
        Route::get('clientes', [ReporteController::class, 'clientes'])->name('reportes.clientes');
        Route::get('seguimientos', [ReporteController::class, 'seguimientos'])->name('reportes.seguimientos');
        Route::get('prospectos', [ReporteController::class, 'prospectos'])->name('reportes.prospectos');
        Route::get('negocios', [ReporteController::class, 'negocios'])->name('reportes.negocios');
        Route::get('forecast', [ReporteController::class, 'forecast'])->name('reportes.forecast');
        Route::get('conversion', [ReporteController::class, 'conversion'])->name('reportes.conversion');
    });

    // ERP
    Route::prefix('erp')->group(function () {
        Route::get('estado', [ERPController::class, 'disponible'])->name('erp.estado');
        Route::get('clientes/{nit}', [ERPController::class, 'buscarPorNit'])->name('erp.clientes.nit');
        Route::get('facturas/{rowid}/detalle', [ERPController::class, 'detalleFactura'])->name('erp.facturas.detalle');
    });

    // Clientes
    Route::apiResource('clientes', ClienteController::class);
    Route::post('clientes/{id}/restore', [ClienteController::class, 'restore'])->name('clientes.restore');

    // Contactos — nested index/store + shallow show/update/destroy
    Route::get('clientes/{cliente}/contactos', [ContactoController::class, 'index'])->name('clientes.contactos.index');
    Route::post('clientes/{cliente}/contactos', [ContactoController::class, 'store'])->name('clientes.contactos.store');
    Route::get('contactos/{contacto}', [ContactoController::class, 'show'])->name('contactos.show');
    Route::put('contactos/{contacto}', [ContactoController::class, 'update'])->name('contactos.update');
    Route::delete('contactos/{contacto}', [ContactoController::class, 'destroy'])->name('contactos.destroy');

    // Seguimientos — ruta estática antes del wildcard
    Route::get('seguimientos/proximos', [SeguimientoController::class, 'proximos'])->name('seguimientos.proximos');
    Route::apiResource('seguimientos', SeguimientoController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

    // Prospectos
    Route::get('prospectos/kanban', [ProspectoController::class, 'kanban'])->name('prospectos.kanban');
    Route::post('prospectos/{prospecto}/convertir', [ProspectoController::class, 'convertir'])->name('prospectos.convertir');
    Route::apiResource('prospectos', ProspectoController::class);

    // Negocios
    Route::get('negocios/kanban', [NegocioController::class, 'kanban'])->name('negocios.kanban');
    Route::get('negocios/forecast', [NegocioController::class, 'forecast'])->name('negocios.forecast');
    Route::apiResource('negocios', NegocioController::class);

    // Usuarios (admin only)
    Route::prefix('usuarios')->group(function () {
        Route::get('/', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::post('/', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::get('{usuario}', [UsuarioController::class, 'show'])->name('usuarios.show');
        Route::put('{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update');
        Route::patch('{usuario}/toggle', [UsuarioController::class, 'toggle'])->name('usuarios.toggle');
    });

    // Maestros comerciales
    Route::prefix('maestros')->group(function () {
        Route::get('/', [MaestroController::class, 'todos'])->name('maestros.todos');
        Route::post('comercial', [MaestroController::class, 'storeMaestro'])->name('maestros.comercial.store');
        Route::put('comercial/{maestro}', [MaestroController::class, 'updateMaestro'])->name('maestros.comercial.update');
        Route::patch('comercial/{maestro}/toggle', [MaestroController::class, 'toggleMaestro'])->name('maestros.comercial.toggle');
        Route::post('pipeline-estado', [MaestroController::class, 'storePipelineEstado'])->name('maestros.pipeline-estado.store');
        Route::put('pipeline-estado/{estado}', [MaestroController::class, 'updatePipelineEstado'])->name('maestros.pipeline-estado.update');
        Route::patch('pipeline-estado/{estado}/toggle', [MaestroController::class, 'togglePipelineEstado'])->name('maestros.pipeline-estado.toggle');
        Route::get('pipeline/{tipo}', [MaestroController::class, 'pipelineEstados'])->name('maestros.pipeline');
        Route::get('{tipo}', [MaestroController::class, 'porTipo'])->name('maestros.tipo');
    });

});
