<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sf_vendedor_equivalencia', function (Blueprint $table) {
            $table->string('rowid_vendedor_siesa', 30)->nullable()->after('cod_vendedor_siesa');
            $table->index(['compania', 'rowid_vendedor_siesa'], 'vend_equiv_rowid_index');
        });
    }

    public function down(): void
    {
        Schema::table('sf_vendedor_equivalencia', function (Blueprint $table) {
            $table->dropIndex('vend_equiv_rowid_index');
            $table->dropColumn('rowid_vendedor_siesa');
        });
    }
};
