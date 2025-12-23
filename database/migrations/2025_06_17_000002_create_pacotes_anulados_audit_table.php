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
        Schema::create('pacotes_anulados_audit', function (Blueprint $table) {
            $table->id();
            
            // Referência ao pacote original
            $table->unsignedBigInteger('pacote_id')->comment('ID do pacote na tabela original');
            
            // Valores originais (antes da anulação) - PRESERVAR HISTÓRICO FINANCEIRO
            $table->decimal('valor_fatura_original', 10, 2)->comment('Valor original da fatura');
            $table->decimal('valor_pago_original', 10, 2)->default(0)->comment('Valor pago original');
            $table->decimal('valor_pendente_original', 10, 2)->default(0)->comment('Valor pendente original');
            $table->decimal('valor_glosa_original', 10, 2)->default(0)->comment('Valor glosa original');
            $table->decimal('valor_pos_lisura_original', 10, 2)->nullable()->comment('Valor pós-lisura original');
            $table->decimal('valor_recursado_original', 10, 2)->default(0)->comment('Valor recursado original');
            $table->decimal('valor_deferido_original', 10, 2)->default(0)->comment('Valor deferido original');
            
            // Snapshot dos dados do pacote no momento da anulação
            $table->string('numero_fatura')->comment('Número da fatura');
            $table->string('ocs_psa_nome')->nullable()->comment('Nome da OCS/PSA no momento');
            $table->string('tipo_pacote_nome')->nullable()->comment('Tipo do pacote no momento');
            $table->string('tipo_conta_nome')->nullable()->comment('Tipo da conta no momento');
            $table->date('data_entrada_original')->comment('Data de entrada original');
            $table->string('localizacao_no_momento', 100)->comment('Localização no momento da anulação');
            $table->string('estado_geral_no_momento', 100)->comment('Estado geral no momento da anulação');
            $table->string('estado_glosa_no_momento', 100)->comment('Estado da glosa no momento da anulação');
            
            // Dados da anulação
            $table->text('motivo_anulacao')->comment('Motivo detalhado da anulação');
            $table->timestamp('data_anulacao')->comment('Data e hora da anulação');
            $table->unsignedBigInteger('usuario_anulacao_id')->comment('Usuário que executou a anulação');
            
            // Campos de controle
            $table->boolean('pode_reverter')->default(true)->comment('Se a anulação pode ser revertida');
            $table->timestamp('data_reversao')->nullable()->comment('Data da reversão, se houver');
            $table->unsignedBigInteger('usuario_reversao_id')->nullable()->comment('Usuário que reverteu');
            $table->text('motivo_reversao')->nullable()->comment('Motivo da reversão');
            
            // Timestamps padrão
            $table->timestamps();
            
            // Índices para performance
            $table->index('pacote_id', 'idx_pacote_id');
            $table->index('data_anulacao', 'idx_data_anulacao');
            $table->index('usuario_anulacao_id', 'idx_usuario_anulacao');
            $table->index(['data_anulacao', 'pode_reverter'], 'idx_anulacao_reverter');
            
            // Foreign Keys
            $table->foreign('pacote_id')->references('id')->on('pacotes')->onDelete('cascade');
            $table->foreign('usuario_anulacao_id')->references('id')->on('users');
            $table->foreign('usuario_reversao_id')->references('id')->on('users');
        });
        
        // Comentário da tabela
        Schema::table('pacotes_anulados_audit', function (Blueprint $table) {
            $table->comment('Auditoria de pacotes anulados - preserva valores originais para contabilidade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pacotes_anulados_audit');
    }
};