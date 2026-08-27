<?php

declare(strict_types=1);

namespace Tests\Feature\SFconnecting\ERP;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExistenciasMPVenceEnDosMesesTest extends TestCase
{
    use RefreshDatabase;

    private function usuarioProduccion(): User
    {
        $this->crearRol('produccion');
        $user = User::factory()->create();
        $user->assignRole('produccion');

        return $user;
    }

    public function test_filtro_dias_vencer_solo_retorna_lotes_dentro_del_rango(): void
    {
        $this->fakeErp->agregarExistenciasMP([
            ['Compania' => 1, 'CodBodega' => 'B1', 'Referencia' => 'R1', 'DescItem' => 'Vence pronto', 'Lote' => 'L1', 'DiasParaVencer' => 15],
            ['Compania' => 1, 'CodBodega' => 'B1', 'Referencia' => 'R2', 'DescItem' => 'Vence justo en el limite', 'Lote' => 'L2', 'DiasParaVencer' => 60],
            ['Compania' => 1, 'CodBodega' => 'B1', 'Referencia' => 'R3', 'DescItem' => 'Vence lejos', 'Lote' => 'L3', 'DiasParaVencer' => 90],
            ['Compania' => 1, 'CodBodega' => 'B1', 'Referencia' => 'R4', 'DescItem' => 'Ya vencido', 'Lote' => 'L4', 'DiasParaVencer' => -5],
            ['Compania' => 1, 'CodBodega' => 'B1', 'Referencia' => 'R5', 'DescItem' => 'Sin fecha', 'Lote' => 'L5', 'DiasParaVencer' => null],
        ]);

        $response = $this->actingAs($this->usuarioProduccion())
            ->get('/consultas-lotes?dias_vencer=60');

        $response->assertOk();
        $response->assertViewHas('total', 2);
        $response->assertSee('Vence pronto');
        $response->assertSee('Vence justo en el limite');
        $response->assertDontSee('Vence lejos');
        $response->assertDontSee('Ya vencido');
        $response->assertDontSee('Sin fecha');
    }

    public function test_sin_filtro_retorna_todos_los_lotes(): void
    {
        $this->fakeErp->agregarExistenciasMP([
            ['Compania' => 1, 'CodBodega' => 'B1', 'Referencia' => 'R1', 'DescItem' => 'Vence pronto', 'Lote' => 'L1', 'DiasParaVencer' => 15],
            ['Compania' => 1, 'CodBodega' => 'B1', 'Referencia' => 'R3', 'DescItem' => 'Vence lejos', 'Lote' => 'L3', 'DiasParaVencer' => 90],
        ]);

        $response = $this->actingAs($this->usuarioProduccion())
            ->get('/consultas-lotes');

        $response->assertOk();
        $response->assertViewHas('total', 2);
    }

    public function test_filtro_se_mantiene_al_paginar(): void
    {
        $filas = [];
        for ($i = 1; $i <= 25; $i++) {
            $filas[] = ['Compania' => 1, 'CodBodega' => 'B1', 'Referencia' => "R{$i}", 'DescItem' => "Item {$i}", 'Lote' => "L{$i}", 'DiasParaVencer' => 30];
        }
        $filas[] = ['Compania' => 1, 'CodBodega' => 'B1', 'Referencia' => 'RX', 'DescItem' => 'Vence lejos', 'Lote' => 'LX', 'DiasParaVencer' => 90];

        $this->fakeErp->agregarExistenciasMP($filas);

        $response = $this->actingAs($this->usuarioProduccion())
            ->get('/consultas-lotes?dias_vencer=60');

        $response->assertOk();
        $response->assertViewHas('total', 25);
        $response->assertViewHas('totalPaginas', 2);
        // El link a la página 2 debe conservar el filtro dias_vencer=60.
        $response->assertSee('dias_vencer=60&amp;page=2', false);

        $pagina2 = $this->actingAs($this->usuarioProduccion())
            ->get('/consultas-lotes?dias_vencer=60&page=2');

        $pagina2->assertOk();
        $pagina2->assertViewHas('total', 25);
        $pagina2->assertDontSee('Vence lejos');
    }
}
