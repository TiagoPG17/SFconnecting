<?php

declare(strict_types=1);

use App\Http\Controllers\Web\AuditoriaWebController;
use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\ClienteWebController;
use App\Http\Controllers\Web\ClientesHuerfanosWebController;
use App\Http\Controllers\Web\CalendarioWebController;
use App\Http\Controllers\Web\ContabilidadWebController;
use App\Http\Controllers\Web\DashboardWebController;
use App\Http\Controllers\Web\MaestroWebController;
use App\Http\Controllers\Web\NegocioWebController;
use App\Http\Controllers\Web\ProspectoWebController;
use App\Http\Controllers\Web\ReporteWebController;
use App\Http\Controllers\Web\SeguimientoWebController;
use App\Http\Controllers\Web\SolicitudCreditoWebController;
use App\Http\Controllers\Web\MapeoVendedorWebController;
use App\Http\Controllers\Web\PresupuestoWebController;
use App\Http\Controllers\Web\AdminGestionWebController;
use App\Http\Controllers\Web\ContactoWebController;
use App\Http\Controllers\Web\UsuarioWebController;
use App\Http\Controllers\Web\ExistenciasMPWebController;
use Illuminate\Support\Facades\Route;

// Auth pública (5 intentos de login por minuto por IP)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthWebController::class, 'login'])->middleware('throttle:5,1');
});
Route::match(['GET', 'POST'], '/logout', [AuthWebController::class, 'logout'])->name('logout')->middleware('auth');

// App — autenticado
Route::middleware(['auth'])->group(function () {

    $homeRedirect = function (\Illuminate\Http\Request $req) {
        $user = $req->user();
        if ($user->hasRole('gerente')) {
            return redirect()->route('dash.gerencial');
        }
        if ($user->hasRole('produccion')) {
            return redirect()->route('existencias-mp.index');
        }
        return redirect()->route('dash.vendedor');
    };

    Route::get('/', $homeRedirect);
    Route::get('/dashboard', $homeRedirect)->name('dashboard');
    Route::get('/dash-comercial', [DashboardWebController::class, 'index'])->name('dash.comercial')->middleware('role:admin|comercial');

    // Dashboards especializados
    Route::get('/mi-desempeno', [DashboardWebController::class, 'vendedor'])->name('dash.vendedor')->middleware('role:comercial|admin');
    Route::get('/gerencial',    [DashboardWebController::class, 'gerencial'])->name('dash.gerencial')->middleware('role:gerente|admin');
    Route::get('/gerencial/clientes-panorama', [DashboardWebController::class, 'clientesPanorama'])->name('gerencial.clientes-panorama')->middleware('role:gerente|admin');
    Route::get('/gerencial/informe-comercial', [DashboardWebController::class, 'informeComercial'])->name('gerencial.informe-comercial')->middleware('role:gerente|admin');
    Route::get('/gerencial/informe-comercial/detalle-cliente', [DashboardWebController::class, 'informeComercialDetalleCliente'])->name('gerencial.informe-comercial.detalle-cliente')->middleware('role:gerente|admin');

    // Calendario
    Route::get('/calendario', [CalendarioWebController::class, 'index'])->name('calendario.index');
    Route::get('/calendario/eventos', [CalendarioWebController::class, 'eventos'])->name('calendario.eventos');

    // Prospectos — estáticas antes del wildcard
    Route::get('/prospectos/kanban',           [ProspectoWebController::class, 'kanban'])->name('prospectos.kanban');
    Route::get('/prospectos/create',           [ProspectoWebController::class, 'create'])->name('prospectos.create')->middleware('role:admin|comercial');
    Route::get('/prospectos/{prospecto}/edit',      [ProspectoWebController::class, 'edit'])->name('prospectos.edit')->middleware('role:admin|comercial');
    Route::get('/prospectos/{prospecto}/convertir', [ProspectoWebController::class, 'convertir'])->name('prospectos.convertir');
    Route::get('/prospectos/{prospecto}',      [ProspectoWebController::class, 'show'])->name('prospectos.show');
    Route::get('/prospectos',                  [ProspectoWebController::class, 'index'])->name('prospectos.index');

    // Negocios — estáticas antes del wildcard
    Route::get('/negocios/kanban',           [NegocioWebController::class, 'kanban'])->name('negocios.kanban');
    Route::get('/negocios/create',           [NegocioWebController::class, 'create'])->name('negocios.create')->middleware('role:admin|comercial');
    Route::get('/negocios/{negocio}/edit',   [NegocioWebController::class, 'edit'])->name('negocios.edit')->middleware('role:admin|comercial');
    Route::get('/negocios/{negocio}',        [NegocioWebController::class, 'show'])->name('negocios.show');
    Route::get('/negocios',                  [NegocioWebController::class, 'index'])->name('negocios.index');

    // Solicitudes de Crédito — estáticas antes del wildcard
    Route::get('/solicitudes-credito/create',              [SolicitudCreditoWebController::class, 'create'])->name('solicitudes-credito.create')->middleware('role:admin|gerente|cartera');
    Route::get('/solicitudes-credito/{solicitudCredito}',  [SolicitudCreditoWebController::class, 'show'])->name('solicitudes-credito.show')->middleware('role:admin|gerente|cartera');
    Route::get('/solicitudes-credito',                     [SolicitudCreditoWebController::class, 'index'])->name('solicitudes-credito.index')->middleware('role:admin|gerente|cartera');

    // Contabilidad — estáticas antes del wildcard
    Route::get('/contabilidad/{cliente}', [ContabilidadWebController::class, 'show'])->name('contabilidad.show')->middleware('role:contabilidad_formacol|contabilidad_contiflex|admin');
    Route::get('/contabilidad',           [ContabilidadWebController::class, 'index'])->name('contabilidad.index')->middleware('role:contabilidad_formacol|contabilidad_contiflex|admin');

    Route::get('/consultas-lotes', [ExistenciasMPWebController::class, 'index'])->name('existencias-mp.index')->middleware('role:produccion|admin');
    Route::patch('/consultas-lotes/actualizar', [ExistenciasMPWebController::class, 'actualizarCampo'])->name('existencias-mp.actualizar')->middleware('role:produccion|admin');

    // Clientes huérfanos (todos los roles)
    Route::patch('/contactos/{contacto}/toggle', [ContactoWebController::class, 'toggle'])->name('contactos.toggle');

    Route::get('/clientes-huerfanos', [ClientesHuerfanosWebController::class, 'index'])->name('clientes-huerfanos.index');
    Route::post('/clientes-huerfanos/{nit}/reclamar', [ClientesHuerfanosWebController::class, 'reclamar'])->name('clientes-huerfanos.reclamar');

    // Clientes
    Route::post('/clientes/sincronizar-cartera', [ClienteWebController::class, 'sincronizarCartera'])->name('clientes.sincronizar');
    Route::get('/clientes/create',        [ClienteWebController::class, 'create'])->name('clientes.create');
    Route::get('/clientes/erp/{nit}',     [ClienteWebController::class, 'showErp'])->name('clientes.erp.show');
    Route::get('/clientes/{cliente}/edit',[ClienteWebController::class, 'edit'])->name('clientes.edit');
    Route::get('/clientes/{cliente}',     [ClienteWebController::class, 'show'])->name('clientes.show');
    Route::get('/clientes',               [ClienteWebController::class, 'index'])->name('clientes.index');

    // Seguimientos
    Route::get('/seguimientos', [SeguimientoWebController::class, 'index'])->name('seguimientos.index');
    Route::patch('/seguimientos/{seguimiento}/resultado', [SeguimientoWebController::class, 'actualizarResultado'])->name('seguimientos.resultado');

    // Maestros y Reportes reales
    Route::get('/maestros', [MaestroWebController::class, 'index'])->name('maestros.index');
    Route::get('/reportes', [ReporteWebController::class, 'index'])->name('reportes.index');

    // Presupuestos (admin y gerente)
    Route::post('/presupuestos/meses', [PresupuestoWebController::class, 'storeMeses'])
        ->name('presupuestos.meses.store')
        ->middleware('role:admin|gerente');

    Route::resource('presupuestos', PresupuestoWebController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->middleware('role:admin|gerente');

    // Mapeo vendedores (solo admin)
    Route::resource('mapeo-vendedores', MapeoVendedorWebController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->middleware('role:admin')
        ->parameters(['mapeo-vendedores' => 'mapeoVendedor']);

    // Auditoría (admin only)
    Route::get('/auditoria', [AuditoriaWebController::class, 'index'])->name('auditoria.index')->middleware('role:admin|gerente|cartera');

    // Gestión de registros (admin only)
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/gestion', [AdminGestionWebController::class, 'index'])->name('admin.gestion.index');

        Route::patch('/admin/gestion/negocios/{negocio}/toggle',   [AdminGestionWebController::class, 'toggleNegocio'])->name('admin.gestion.negocios.toggle');
        Route::delete('/admin/gestion/negocios/{negocio}',         [AdminGestionWebController::class, 'destroyNegocio'])->name('admin.gestion.negocios.destroy');
        Route::patch('/admin/gestion/negocios/{id}/restore',       [AdminGestionWebController::class, 'restoreNegocio'])->name('admin.gestion.negocios.restore');

        Route::patch('/admin/gestion/prospectos/{prospecto}/toggle', [AdminGestionWebController::class, 'toggleProspecto'])->name('admin.gestion.prospectos.toggle');
        Route::delete('/admin/gestion/prospectos/{prospecto}',       [AdminGestionWebController::class, 'destroyProspecto'])->name('admin.gestion.prospectos.destroy');
        Route::patch('/admin/gestion/prospectos/{id}/restore',       [AdminGestionWebController::class, 'restoreProspecto'])->name('admin.gestion.prospectos.restore');
    });

    // Usuarios (admin only)
    Route::get('/usuarios/create',        [UsuarioWebController::class, 'create'])->name('usuarios.create');
    Route::get('/usuarios/{usuario}/edit',[UsuarioWebController::class, 'edit'])->name('usuarios.edit');
    Route::get('/usuarios',               [UsuarioWebController::class, 'index'])->name('usuarios.index');
});
