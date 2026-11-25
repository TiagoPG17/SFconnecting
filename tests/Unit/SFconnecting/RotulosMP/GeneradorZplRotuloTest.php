<?php

declare(strict_types=1);

namespace Tests\Unit\SFconnecting\RotulosMP;

use App\Domain\RotulosMP\Services\GeneradorZplRotulo;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class GeneradorZplRotuloTest extends TestCase
{
    private function renglon(array $cambios = []): array
    {
        return array_merge([
            'id_row' => 833803, 'pedido' => 'PV37697-139852-0014111-2', 'documento' => 'SLI299428',
            'formula' => '27661', 'cliente' => 'CLICK COSMETICS S.A.S.', 'color' => 'BLANCO', 'material' => 'PP',
            'proceso' => 'TRANSFORMACION', 'capa' => 'GENERAL', 'ref' => 'MP-011008',
            'nombre_material' => 'POLIPROPILENO NO.RT100N C/R BRASKEM', 'cantidad' => '103000.00000',
            'unidad' => 'GRS', 'lote' => 'RSGHPG038E',
        ], $cambios);
    }

    public function test_etiqueta_con_el_formato_de_sgp_y_el_peso_del_costal(): void
    {
        $zpl = (new GeneradorZplRotulo())->etiqueta($this->renglon(), '25000', 2, 5, Carbon::create(2026, 11, 25));

        $this->assertSame(
            '^XA^LL0799^PW799'
            .'^FT27,50 ^A0N,36,36^FH\^FDPEDIDO: PV37697-139852-0014111-2^FS'
            .'^FT27,95 ^A0N,26,26^FH\^FDDOCUMENTO: SLI299428        FORMULA: 27661^FS'
            .'^FT27,135^A0N,27,27^FH\^FDCLICK COSMETICS S.A.S.^FS'
            .'^FT27,175^A0N,26,26^FH\^FDCOLOR: BLANCO   MATERIAL: PP^FS'
            .'^FT27,215^A0N,26,26^FH\^FDPROCESO: TRANSFORMACION^FS'
            .'^FT27,260^A0N,36,36^FH\^FDCAPA: GENERAL    REF:MP-011008^FS'
            .'^FT27,305^A0N,36,36^FH\^FDPOLIPROPILENO NO.RT100N C/R BRASKEM^FS'
            .'^FT27,350^A0N,36,36^FH\^FDCANTIDAD: 25000 GRS     LOTE:RSGHPG038E^FS'
            .'^FT27,390^A0N,23,23^FH\^FDROT: 2/5                  25/11/2026^FS'
            .'^PQ1,0,1,Y^XZ',
            $zpl
        );
    }

    public function test_limpia_caracteres_que_rompen_el_zpl(): void
    {
        $zpl = (new GeneradorZplRotulo())->etiqueta(
            $this->renglon(['cliente' => "ACME ^XZ~JR S.A.\\S\nÑANDÚ", 'formula' => null]),
            '1', 1, 1, Carbon::create(2026, 1, 2)
        );

        $this->assertStringContainsString('^FDACME XZ JR S.A. S ÑANDÚ^FS', $zpl);
        $this->assertStringContainsString('FORMULA: ^FS', $zpl);
        $this->assertSame(1, substr_count($zpl, '^XZ'));
    }

    public function test_documento_lleva_la_cabecera_una_sola_vez(): void
    {
        $gen = new GeneradorZplRotulo();
        $doc = $gen->documento(['^XA1^XZ', '^XA2^XZ']);

        $this->assertSame(GeneradorZplRotulo::CABECERA.'^XA1^XZ^XA2^XZ', $doc);
    }
}
