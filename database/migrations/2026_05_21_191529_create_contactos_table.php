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
        Schema::create('contactos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('cargo', 100)->nullable();
            $table->string('email')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->boolean('principal')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('cliente_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contactos');
    }
};
