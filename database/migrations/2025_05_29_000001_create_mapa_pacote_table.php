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
        // Modificar a tabela mapas para remover campos que agora vão para a tabela de junção
        Schema::table('mapas', function (Blueprint $table) {
            $table->dropForeign(['pacote_id']);
            $table->dropColumn([
                'pacote_id',
                'valor_parcial',
                'empenho',
                'data_empenho',
                'nota_fiscal',
                'data_nota_fiscal'
            ]);
        });

        // Criar tabela de junção
        Schema::create('mapa_pacote', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mapa_id');
            $table->unsignedBigInteger('pacote_id');
            $table->decimal('valor_parcial', 10, 2);
            $table->string('empenho');
            $table->date('data_empenho');
            $table->string('nota_fiscal');
            $table->date('data_nota_fiscal');
            $table->timestamps();
            
            $table->foreign('mapa_id')->references('id')->on('mapas')->onDelete('cascade');
            $table->foreign('pacote_id')->references('id')->on('pacotes')->onDelete('cascade');
            
            // Cada pacote só pode aparecer uma vez no mesmo mapa
            $table->unique(['mapa_id', 'pacote_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mapa_pacote');
        
        // Restaurar os campos na tabela mapas
        Schema::table('mapas', function (Blueprint $table) {
            $table->unsignedBigInteger('pacote_id')->nullable();
            $table->decimal('valor_parcial', 10, 2)->nullable();
            $table->string('empenho')->nullable();
            $table->date('data_empenho')->nullable();
            $table->string('nota_fiscal')->nullable();
            $table->date('data_nota_fiscal')->nullable();
            
            $table->foreign('pacote_id')->references('id')->on('pacotes');
        });
    }
};