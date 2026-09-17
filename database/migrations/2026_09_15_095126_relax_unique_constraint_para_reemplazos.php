<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // El unique(compania, cod_vendedor_siesa) impedía el caso de reemplazo:
        // el reemplazante necesita una fila con el MISMO código que el titular.
        // La regla "un código solo puede tener un dueño real" ahora se valida en
        // la app (VendedorEquivalenciaRepository::existeCodigo, filtrando
        // es_reemplazo = false), y aquí dejamos un índice normal para las consultas.
        Schema::table('sf_vendedor_equivalencia', function (Blueprint $table) {
            $table->dropUnique('vend_equiv_codigo_unique');
            $table->index(['compania', 'cod_vendedor_siesa'], 'vend_equiv_codigo_index');
        });
    }

    public function down(): void
    {
        Schema::table('sf_vendedor_equivalencia', function (Blueprint $table) {
            $table->dropIndex('vend_equiv_codigo_index');
            $table->unique(['compania', 'cod_vendedor_siesa'], 'vend_equiv_codigo_unique');
        });
    }
};
