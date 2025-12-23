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
        Schema::create('mapas', function (Blueprint $table) {
            $table->id();
            $table->string('numero_mapa')->unique();
            $table->date('data_criacao');
            $table->unsignedBigInteger('pacote_id');
            $table->decimal('valor_parcial', 10, 2);
            $table->string('empenho');
            $table->date('data_empenho');
            $table->string('nota_fiscal');
            $table->date('data_nota_fiscal');
            $table->timestamps();
            
            $table->foreign('pacote_id')->references('id')->on('pacotes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mapas');
    }
};
