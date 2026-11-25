<?php

declare(strict_types=1);

namespace Tests\Unit\SFconnecting\RotulosMP;

use App\Domain\RotulosMP\Exceptions\RotuloException;
use App\Domain\RotulosMP\Services\CalculadoraCostales;
use PHPUnit\Framework\TestCase;

class CalculadoraCostalesTest extends TestCase
{
    private CalculadoraCostales $calc;

    protected function setUp(): void
    {
        $this->calc = new CalculadoraCostales();
    }

    public function test_costales_completos_mas_saldo(): void
    {
        $this->assertSame(
            ['25000', '25000', '25000', '25000', '3000'],
            $this->calc->calcular('103000.00000', '25000', 100)
        );
    }

    public function test_division_exacta_sin_saldo(): void
    {
        $this->assertSame(['25000', '25000', '25000', '25000'], $this->calc->calcular('100000.00000', '25000', 100));
    }

    public function test_peso_mayor_o_igual_al_total_da_una_etiqueta_con_el_total(): void
    {
        $this->assertSame(['3000'], $this->calc->calcular('3000.00000', '25000', 100));
        $this->assertSame(['25000'], $this->calc->calcular('25000', '25000', 100));
    }

    public function test_decimales_suman_exactamente_el_total(): void
    {
        $etiquetas = $this->calc->calcular('10.50000', '2.2', 100);

        $this->assertSame(['2.2', '2.2', '2.2', '2.2', '1.7'], $etiquetas);
        $suma = array_sum(array_map(fn ($e) => $this->calc->aEscala($e), $etiquetas));
        $this->assertSame($this->calc->aEscala('10.5'), $suma);
    }

    public function test_peso_cero_o_negativo_falla(): void
    {
        foreach (['0', '0.00000', '-5'] as $peso) {
            try {
                $this->calc->calcular('103000', $peso, 100);
                $this->fail("Debió fallar con peso {$peso}");
            } catch (RotuloException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_total_cero_falla(): void
    {
        $this->expectException(RotuloException::class);
        $this->calc->calcular('0.00000', '25000', 100);
    }

    public function test_tope_evita_miles_de_etiquetas_por_error_de_unidad(): void
    {
        // 25 escrito pensando en kilos sobre un material en GRS → 4120 etiquetas.
        $this->expectException(RotuloException::class);
        $this->expectExceptionMessage('4120');
        $this->calc->calcular('103000.00000', '25', 100);
    }

    public function test_texto_no_numerico_falla(): void
    {
        $this->expectException(RotuloException::class);
        $this->calc->calcular('103000', '25.000,5', 100);
    }

    public function test_formatea_sin_punto_cero(): void
    {
        $this->assertSame('25000', $this->calc->formatear($this->calc->aEscala('25000.00000')));
        $this->assertSame('2500.5', $this->calc->formatear($this->calc->aEscala('2500.50000')));
        $this->assertSame('0.00001', $this->calc->formatear($this->calc->aEscala('0.00001')));
    }
}
