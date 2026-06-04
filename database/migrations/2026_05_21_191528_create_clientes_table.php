<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('razon_social');
            $table->string('nit', 20)->unique();
            $table->string('email')->unique()->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('direccion')->nullable();
            $table->enum('estado', ['activo', 'inactivo', 'prospecto'])->default('prospecto');
            $table->text('notas')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['estado', 'ciudad']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
