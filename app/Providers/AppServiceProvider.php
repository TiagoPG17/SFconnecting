<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Auditoria\Repositories\AuditoriaRepository;
use App\Domain\Auditoria\Repositories\AuditoriaRepositoryInterface;
use App\Domain\Clientes\Models\Cliente;
use App\Domain\Clientes\Models\Contacto;
use App\Domain\Clientes\Policies\ClientePolicy;
use App\Domain\Clientes\Policies\ContactoPolicy;
use App\Domain\Clientes\Repositories\ClienteRepository;
use App\Domain\Clientes\Repositories\ClienteRepositoryInterface;
use App\Domain\Clientes\Repositories\ContactoRepository;
use App\Domain\Clientes\Repositories\ContactoRepositoryInterface;
use App\Domain\Dashboard\Repositories\DashboardGerencialRepository;
use App\Domain\Dashboard\Repositories\DashboardGerencialRepositoryInterface;
use App\Domain\Dashboard\Repositories\DashboardRepository;
use App\Domain\Dashboard\Repositories\DashboardRepositoryInterface;
use App\Domain\Dashboard\Repositories\DashboardVendedorRepository;
use App\Domain\Dashboard\Repositories\DashboardVendedorRepositoryInterface;
use App\Domain\Dashboard\Repositories\PresupuestoRepository;
use App\Domain\Dashboard\Repositories\PresupuestoRepositoryInterface;
use App\Domain\Dashboard\Repositories\VendedorEquivalenciaRepository;
use App\Domain\Dashboard\Repositories\VendedorEquivalenciaRepositoryInterface;
use App\Domain\ERP\Contracts\ERPRepositoryInterface;
use App\Domain\ERP\Fakes\FakeERPRepository;
use App\Domain\Maestros\Repositories\MaestroRepository;
use App\Domain\Maestros\Repositories\MaestroRepositoryInterface;
use App\Domain\Negocios\Models\Negocio;
use App\Domain\Negocios\Policies\NegocioPolicy;
use App\Domain\Negocios\Repositories\NegocioRepository;
use App\Domain\Negocios\Repositories\NegocioRepositoryInterface;
use App\Domain\Prospectos\Models\Prospecto;
use App\Domain\Prospectos\Policies\ProspectoPolicy;
use App\Domain\Prospectos\Repositories\ProspectoRepository;
use App\Domain\Prospectos\Repositories\ProspectoRepositoryInterface;
use App\Domain\Reportes\Repositories\ReporteRepository;
use App\Domain\Reportes\Repositories\ReporteRepositoryInterface;
use App\Domain\Seguimientos\Models\Seguimiento;
use App\Domain\Seguimientos\Policies\SeguimientoPolicy;
use App\Domain\Seguimientos\Repositories\SeguimientoRepository;
use App\Domain\Seguimientos\Repositories\SeguimientoRepositoryInterface;
use App\Domain\SolicitudesCredito\Models\SolicitudCredito;
use App\Domain\SolicitudesCredito\Policies\SolicitudCreditoPolicy;
use App\Domain\SolicitudesCredito\Repositories\SolicitudCreditoRepository;
use App\Domain\SolicitudesCredito\Repositories\SolicitudCreditoRepositoryInterface;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuditoriaRepositoryInterface::class, AuditoriaRepository::class);
        $this->app->bind(ClienteRepositoryInterface::class, ClienteRepository::class);
        $this->app->bind(ContactoRepositoryInterface::class, ContactoRepository::class);
        $this->app->bind(SeguimientoRepositoryInterface::class, SeguimientoRepository::class);
        $this->app->bind(DashboardRepositoryInterface::class, DashboardRepository::class);
        $this->app->bind(DashboardVendedorRepositoryInterface::class, DashboardVendedorRepository::class);
        $this->app->bind(DashboardGerencialRepositoryInterface::class, DashboardGerencialRepository::class);
        $this->app->bind(PresupuestoRepositoryInterface::class, PresupuestoRepository::class);
        $this->app->bind(VendedorEquivalenciaRepositoryInterface::class, VendedorEquivalenciaRepository::class);
        $this->app->bind(ReporteRepositoryInterface::class, ReporteRepository::class);
        $this->app->bind(ProspectoRepositoryInterface::class, ProspectoRepository::class);
        $this->app->bind(NegocioRepositoryInterface::class, NegocioRepository::class);
        $this->app->bind(MaestroRepositoryInterface::class, MaestroRepository::class);
        $this->app->bind(SolicitudCreditoRepositoryInterface::class, SolicitudCreditoRepository::class);

        $this->app->bind(ERPRepositoryInterface::class, function () {
            if (app()->environment('testing') || config('database.connections.erp_contiflex.host') === '') {
                return new FakeERPRepository();
            }

            return new \App\Domain\ERP\Repositories\ContiflexERPRepository();
        });
    }

    public function boot(): void
    {
        Paginator::defaultView('pagination.default');
        Paginator::defaultSimpleView('pagination.simple');

        Gate::policy(Cliente::class, ClientePolicy::class);
        Gate::policy(Contacto::class, ContactoPolicy::class);
        Gate::policy(Seguimiento::class, SeguimientoPolicy::class);
        Gate::policy(Prospecto::class, ProspectoPolicy::class);
        Gate::policy(Negocio::class, NegocioPolicy::class);
        Gate::policy(SolicitudCredito::class, SolicitudCreditoPolicy::class);

        View::composer('components.layouts.app', function ($view) {
            if (auth()->check()) {
                $notificaciones = Seguimiento::where('user_id', auth()->id())
                    ->where('resultado', 'pendiente')
                    ->where(function ($q) {
                        $q->whereDate('fecha_seguimiento', '<=', now())
                          ->orWhere(function ($q2) {
                              $q2->whereNotNull('proxima_fecha')
                                 ->whereDate('proxima_fecha', '<=', now());
                          });
                    })
                    ->with(['cliente:id,razon_social', 'prospecto:id,empresa'])
                    ->orderByRaw('COALESCE(proxima_fecha, fecha_seguimiento)')
                    ->limit(15)
                    ->get();
            } else {
                $notificaciones = collect();
            }
            $view->with('notificaciones', $notificaciones);
        });
    }
}
