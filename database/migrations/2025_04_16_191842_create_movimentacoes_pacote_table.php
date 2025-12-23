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
        Schema::create('movimentacoes_pacote', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pacote_id')->constrained('pacotes');
            $table->string('acao');
            $table->text('mensagem');
            $table->text('observacao')->nullable();
            $table->string('localizacao_pos_acao');
            $table->string('estado_geral');
            $table->string('estado_glosa');
            $table->foreignId('usuario_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimentacoes_pacote');
    }
};
