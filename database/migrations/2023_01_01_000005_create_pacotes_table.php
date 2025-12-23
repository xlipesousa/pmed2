<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePacotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pacotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ocs_psa_id')->constrained('ocs_psa');
            $table->foreignId('tipo_id')->constrained('tipos_pacote');
            $table->foreignId('tipo_conta_id')->nullable()->constrained('tipos_conta');
            $table->string('numero_fatura');
            $table->date('data_entrada');
            $table->decimal('valor_fatura', 10, 2);
            $table->decimal('valor_glosa', 10, 2)->default(0);
            $table->decimal('valor_pos_lisura', 10, 2)->nullable();
            $table->decimal('valor_pago', 10, 2)->default(0);
            $table->decimal('valor_pendente', 10, 2)->nullable();
            $table->enum('estado_geral', ['Normal', 'Aguardando Limite de Crédito', 'Arquivado'])->default('Normal');
            $table->enum('estado_glosa', [
                'Não identificada', 
                'Glosa identificada', 
                'Recurso pendente', 
                'Recurso deferido', 
                'Recurso indeferido'
            ])->default('Não identificada');
            $table->enum('localizacao_atual', [
                'Protocolo', 
                'Lisura', 
                'SIRE', 
                'Glosa', 
                'Arquivo', 
                'Arquivados'
            ])->default('Protocolo');
            $table->enum('localizacao_anterior', [
                'Protocolo', 
                'Lisura', 
                'SIRE', 
                'Glosa', 
                'Arquivo', 
                'Arquivados'
            ])->nullable();
            $table->string('ultima_acao')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pacotes');
    }
}