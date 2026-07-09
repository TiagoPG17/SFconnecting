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

    public function test_busca_clientes_por_nombre_parcial(): void
    {
        $this->erp->agregarCliente('111', ['nombre' => 'Comercial Torres']);
        $this->erp->agregarCliente('222', ['nombre' => 'Distribuidora Torres']);
        $this->erp->agregarCliente('333', ['nombre' => 'Empresa XYZ']);

        $resultados = $this->erp->clientesPorNombre('Torres');

        $this->assertCount(2, $resultados);
    }

    public function test_retorna_array_vacio_sin_coincidencias(): void
    {
        $resultados = $this->erp->clientesPorNombre('NoExiste');

        $this->assertIsArray($resultados);
        $this->assertEmpty($resultados);
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

    public function test_retorna_documentos_de_cliente(): void
    {
        $documentos = [
            ['numero' => 'FV-001', 'valor' => 1500000],
            ['numero' => 'FV-002', 'valor' => 800000],
        ];
        $this->erp->agregarDocumentos('900123456', $documentos);

        $resultado = $this->erp->documentosPorCliente('900123456');

        $this->assertCount(2, $resultado);
    }

    public function test_retorna_array_vacio_para_cliente_sin_documentos(): void
    {
        $resultado = $this->erp->documentosPorCliente('000000000');

        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    public function test_retorna_saldo_de_cliente(): void
    {
        $this->erp->agregarSaldo('900123456', ['saldo_total' => 2300000, 'vencido' => 500000]);

        $saldo = $this->erp->saldoPorCliente('900123456');

        $this->assertNotNull($saldo);
        $this->assertSame(2300000, $saldo['saldo_total']);
    }

    public function test_retorna_null_para_cliente_sin_saldo(): void
    {
        $saldo = $this->erp->saldoPorCliente('000');

        $this->assertNull($saldo);
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

