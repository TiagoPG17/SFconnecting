<?php

declare(strict_types=1);

namespace Tests\Unit\SFconnecting\ERP;

use App\Domain\ERP\Exceptions\ERPConnectionException;
use App\Domain\ERP\Fakes\FakeERPRepository;
use Tests\TestCase;

class FakeERPRepositoryTest extends TestCase
{
    private FakeERPRepository $erp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->erp = new FakeERPRepository();
    }

    public function test_disponible_por_defecto(): void
    {
        $this->assertTrue($this->erp->isAvailable());
    }

    public function test_retorna_null_cuando_nit_no_existe(): void
    {
        $resultado = $this->erp->clientePorNit('999999999');

        $this->assertNull($resultado);
    }

    public function test_retorna_cliente_registrado_por_nit(): void
    {
        $this->erp->agregarCliente('900123456', ['nombre' => 'Empresa ABC', 'ciudad' => 'BogotÃ¡']);

        $cliente = $this->erp->clientePorNit('900123456');

        $this->assertNotNull($cliente);
        $this->assertSame('Empresa ABC', $cliente['nombre']);
        $this->assertSame('900123456', $cliente['nit']);
    }

    public function test_lanza_excepcion_cuando_se_simula_desconexion(): void
    {
        $this->erp->simularDesconexion();

        $this->expectException(ERPConnectionException::class);

        $this->erp->clientePorNit('123');
    }

    public function test_no_disponible_tras_simular_desconexion(): void
    {
        $this->erp->simularDesconexion();

        $this->assertFalse($this->erp->isAvailable());
    }

    public function test_lanza_excepcion_al_simular_error_en_consulta(): void
    {
        $this->erp->simularErrorEnConsulta();

        $this->expectException(ERPConnectionException::class);
        $this->expectExceptionMessageMatches('/Error simulado/');

        $this->erp->clientePorNit('123');
    }

    public function test_reconecta_tras_simular_conexion(): void
    {
        $this->erp->simularDesconexion();
        $this->erp->simularConexion();

        $this->assertTrue($this->erp->isAvailable());
        $this->assertNull($this->erp->clientePorNit('999'));
    }

    public function test_retorna_cartera_registrada_por_nit(): void
    {
        $filas = [
            ['TIPO_DOCTO' => 'FV', 'NUM_DOCTO' => 1, 'SALDO' => 500000, 'DIAS_VENCIDO' => 10, 'TRAMO_AGING' => '1-30'],
            ['TIPO_DOCTO' => 'FV', 'NUM_DOCTO' => 2, 'SALDO' => 300000, 'DIAS_VENCIDO' => 45, 'TRAMO_AGING' => '31-60'],
        ];
        $this->erp->agregarCartera('900123456', $filas);

        $resultado = $this->erp->carteraPorNit('900123456');

        $this->assertCount(2, $resultado);
        $this->assertSame(500000, $resultado[0]['SALDO']);
    }

    public function test_retorna_array_vacio_para_cliente_sin_cartera(): void
    {
        $resultado = $this->erp->carteraPorNit('000000000');

        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    public function test_lanza_excepcion_cartera_cuando_se_simula_desconexion(): void
    {
        $this->erp->simularDesconexion();

        $this->expectException(ERPConnectionException::class);

        $this->erp->carteraPorNit('900123456');
    }
}

