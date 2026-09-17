<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // El FK de asesor_id necesita un índice que lo respalde; vend_equiv_unique
        // cumplía ese rol como índice compuesto. Se agrega uno dedicado antes de
        // quitarlo para que MySQL no rechace el drop.
        Schema::table('sf_vendedor_equivalencia', function (Blueprint $table) {
            $table->index('asesor_id', 'vend_equiv_asesor_index');
        });

        Schema::table('sf_vendedor_equivalencia', function (Blueprint $table) {
            $table->dropUnique('vend_equiv_unique');
            $table->unique(['compania', 'cod_vendedor_siesa'], 'vend_equiv_codigo_unique');
        });
    }

    public function down(): void
    {
        Schema::table('sf_vendedor_equivalencia', function (Blueprint $table) {
            $table->unique(['asesor_id', 'compania'], 'vend_equiv_unique');
        });

        Schema::table('sf_vendedor_equivalencia', function (Blueprint $table) {
            $table->dropUnique('vend_equiv_codigo_unique');
            $table->dropIndex('vend_equiv_asesor_index');
        });
    }
};
