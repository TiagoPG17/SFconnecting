<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Clientes\DTOs\CrearClienteDTO;
use App\Domain\Clientes\Exceptions\ClienteDuplicadoException;
use App\Domain\Clientes\Repositories\ClienteRepositoryInterface;
use App\Domain\Clientes\Services\ClienteService;
use App\Domain\Dashboard\Models\VendedorEquivalencia;
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
    ) {}

    public function index(Request $request): View
    {
        $user      = $request->user();
        $esAsesor  = $user->hasRole('comercial');
        $compania  = $this->companiaDelUsuario($user->id, $esAsesor);

        $nitsEnCrm = \App\Domain\Clientes\Models\Cliente::pluck('nit')->toArray();

        $huerfanos     = [];
        $erpDisponible = false;

        try {
            if ($this->erp->isAvailable()) {
                $erpDisponible = true;
                $huerfanos = $this->erp->clientesHuerfanos($compania, $nitsEnCrm, 150);
            }
        } catch (\Throwable) {}

        return view('clientes.huerfanos', compact('huerfanos', 'erpDisponible', 'compania', 'esAsesor'));
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
                ciudad:      $request->input('ciudad'),
                estado:      'activo',
                notas:       'Reactivado desde lista de clientes huérfanos (CIA ' . $compania . ').',
            );

            $cliente = $this->clienteService->crear($dto);

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
        if ($esAsesor) {
            $compania = VendedorEquivalencia::where('asesor_id', $userId)
                ->where('activo', true)
                ->value('compania');

            return $compania ? (int) $compania : (int) config('crm.compania', 1);
        }

        return (int) config('crm.compania', 1);
    }
}
