<?php
// Criar nova migration
// php artisan make:migration make_mapa_fields_nullable

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
        Schema::table('mapa_pacote', function (Blueprint $table) {
            $table->string('empenho')->nullable()->change();
            $table->date('data_empenho')->nullable()->change();
            $table->string('nota_fiscal')->nullable()->change();
            $table->date('data_nota_fiscal')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mapa_pacote', function (Blueprint $table) {
            $table->string('empenho')->nullable(false)->change();
            $table->date('data_empenho')->nullable(false)->change();
            $table->string('nota_fiscal')->nullable(false)->change();
            $table->date('data_nota_fiscal')->nullable(false)->change();
        });
    }
};
