<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovimentacoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movimentacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pacote_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->enum('tipo', [
                'Movimento de pacote',
                'Edição', 
                'Criação de novo Pacote',
                'Notificação de Existência de Glosa',
                'Retirada de Ofício de Glosa',
                'Aguardando Recurso de Glosa',
                'Recurso não recebido',
                'Recebimento de recurso de Glosa',
                'Recurso indeferido',
                'Recurso deferido',
                'Pacote arquivado',
                'Aguardando Limite de Crédito',
                'Pagamento'
            ]);
            $table->string('origem')->nullable();
            $table->string('destino')->nullable();
            $table->text('descricao');
            $table->enum('estado_geral', ['Normal', 'Aguardando Limite de Crédito', 'Arquivado']);
            $table->enum('estado_glosa', [
                'Não identificada', 
                'Glosa identificada', 
                'Recurso pendente', 
                'Recurso deferido', 
                'Recurso indeferido'
            ]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movimentacoes');
    }
}