<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Auditoria\Models\ActividadLog;
use App\Domain\Clientes\DTOs\CrearClienteDTO;
use App\Domain\Clientes\Exceptions\ClienteDuplicadoException;
use App\Domain\Clientes\Repositories\ClienteRepositoryInterface;
use App\Domain\Clientes\Services\ClienteService;
use App\Domain\Dashboard\Repositories\DashboardVendedorRepositoryInterface;
use App\Domain\ERP\Contracts\ERPRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientesHuerfanosWebController extends Controller
{
    public function __construct(
        private readonly ERPRepositoryInterface $erp,
        private readonly ClienteService $clienteService,
        private readonly ClienteRepositoryInterface $clienteRepo,
        private readonly DashboardVendedorRepositoryInterface $vendedorRepo,
    ) {}

    public function index(Request $request): View
    {
        $user      = $request->user();
        $esAsesor  = $user->hasRole('comercial');
        $compania  = $this->companiaDelUsuario($user->id, $esAsesor);

        $nitsEnCrm = \App\Domain\Clientes\Models\Cliente::pluck('nit')->toArray();

        $huerfanos      = [];
        $totalHuerfanos = 0;
        $erpDisponible  = false;
        $porPagina      = 50;
        $pagina         = max(1, (int) $request->query('page', 1));

        try {
            if ($this->erp->isAvailable()) {
                $erpDisponible  = true;
                $totalHuerfanos = $this->erp->countClientesHuerfanos($compania, $nitsEnCrm);
                $offset         = ($pagina - 1) * $porPagina;
                $huerfanos      = $this->erp->clientesHuerfanos($compania, $nitsEnCrm, $porPagina, $offset);
            }
        } catch (\Throwable) {}

        $totalPaginas = $totalHuerfanos > 0 ? (int) ceil($totalHuerfanos / $porPagina) : 1;
        $pagina       = min($pagina, $totalPaginas);

        return view('clientes.huerfanos', compact(
            'huerfanos', 'totalHuerfanos', 'erpDisponible',
            'compania', 'esAsesor', 'pagina', 'totalPaginas', 'porPagina'
        ));
    }

    public function reclamar(Request $request, string $nit): RedirectResponse
    {
        $user     = $request->user();
        $esAsesor = $user->hasRole('comercial');
        $compania = $this->companiaDelUsuario($user->id, $esAsesor);

        try {
            $dto = new CrearClienteDTO(
                razonSocial: $request->input('razon_social', $nit),
                nit:         $nit,
                userId:      $user->id,
                compania:    $compania,
                ciudad:      $request->input('ciudad'),
                estado:      'activo',
                notas:       'Reactivado desde lista de clientes huérfanos (CIA ' . $compania . ').',
            );

            $cliente = $this->clienteService->crear($dto);

            ActividadLog::registrar('reclamar', 'clientes', "Cliente '{$cliente->razon_social}' (NIT: {$cliente->nit}) reclamado desde huérfanos (CIA {$compania})", $user->id);

            return redirect()
                ->route('clientes.show', $cliente)
                ->with('success', "¡Cliente asignado a tu cartera! A reactivarlo.");

        } catch (ClienteDuplicadoException $e) {
            return redirect()
                ->route('clientes-huerfanos.index')
                ->withErrors(['nit' => $e->getMessage()]);
        }
    }

    private function companiaDelUsuario(int $userId, bool $esAsesor): int
    {
        return $esAsesor
            ? $this->vendedorRepo->companiaPrincipal($userId)
            : (int) config('crm.compania', 1);
    }
}
