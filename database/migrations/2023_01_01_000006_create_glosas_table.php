<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlosasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('glosas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pacote_id')->constrained();
            $table->foreignId('motivo_glosa_id')->nullable()->constrained('motivos_glosa');
            $table->decimal('valor', 10, 2);
            $table->text('descricao')->nullable();
            $table->decimal('valor_recursado', 10, 2)->default(0);
            $table->decimal('valor_deferido', 10, 2)->default(0);
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
        Schema::dropIfExists('glosas');
    }
}