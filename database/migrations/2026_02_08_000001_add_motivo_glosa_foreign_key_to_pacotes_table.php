<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function foreignKeyExists(string $table, string $column, ?string $constraint = null): bool
    {
        $database = DB::getDatabaseName();
        $rows = DB::select(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$database, $table, $column]
        );

        if (empty($rows)) {
            return false;
        }

        if ($constraint === null) {
            return true;
        }

        foreach ($rows as $row) {
            if ($row->CONSTRAINT_NAME === $constraint) {
                return true;
            }
        }

        return false;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('pacotes', 'motivo_glosa_id')) {
            Schema::table('pacotes', function (Blueprint $table) {
                $table->unsignedBigInteger('motivo_glosa_id')->nullable()->after('tipo_conta_id');
            });
        }

        $constraintName = 'pacotes_motivo_glosa_id_foreign';
        if (!$this->foreignKeyExists('pacotes', 'motivo_glosa_id', $constraintName)) {
            Schema::table('pacotes', function (Blueprint $table) {
                $table->foreign('motivo_glosa_id')
                    ->references('id')
                    ->on('motivos_glosa')
                    ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $constraintName = 'pacotes_motivo_glosa_id_foreign';
        if ($this->foreignKeyExists('pacotes', 'motivo_glosa_id', $constraintName)) {
            Schema::table('pacotes', function (Blueprint $table) use ($constraintName) {
                $table->dropForeign($constraintName);
            });
        }
    }
};
