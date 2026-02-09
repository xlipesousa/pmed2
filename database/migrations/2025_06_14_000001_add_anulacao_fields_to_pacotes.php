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
        if (!Schema::hasColumn('pacotes', 'anulado')) {
            Schema::table('pacotes', function (Blueprint $table) {
                $table->boolean('anulado')->default(false)->after('localizacao_fisica');
            });
        }

        if (!Schema::hasColumn('pacotes', 'data_anulacao')) {
            Schema::table('pacotes', function (Blueprint $table) {
                $table->timestamp('data_anulacao')->nullable()->after('anulado');
            });
        }

        if (!Schema::hasColumn('pacotes', 'motivo_anulacao')) {
            Schema::table('pacotes', function (Blueprint $table) {
                $table->text('motivo_anulacao')->nullable()->after('data_anulacao');
            });
        }

        if (!Schema::hasColumn('pacotes', 'usuario_anulacao_id')) {
            Schema::table('pacotes', function (Blueprint $table) {
                $table->unsignedBigInteger('usuario_anulacao_id')->nullable()->after('motivo_anulacao');
            });
        }

        try {
            $constraintName = 'pacotes_usuario_anulacao_id_foreign';
            if (!$this->foreignKeyExists('pacotes', 'usuario_anulacao_id', $constraintName)) {
                Schema::table('pacotes', function (Blueprint $table) {
                    $table->foreign('usuario_anulacao_id')
                        ->references('id')
                        ->on('users')
                        ->onDelete('set null');
                });
            }
        } catch (\Exception $e) {
            // Ignora erro de FK duplicada ou ausente
        }

        try {
            Schema::table('pacotes', function (Blueprint $table) {
                $table->index(['anulado']);
            });
        } catch (\Exception $e) {
            // Ignora indice ja existente
        }

        try {
            Schema::table('pacotes', function (Blueprint $table) {
                $table->index(['anulado', 'localizacao_atual']);
            });
        } catch (\Exception $e) {
            // Ignora indice ja existente
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('pacotes', function (Blueprint $table) {
                $table->dropForeign(['usuario_anulacao_id']);
            });
        } catch (\Exception $e) {
            // Ignora erro se a FK nao existir
        }

        try {
            Schema::table('pacotes', function (Blueprint $table) {
                $table->dropIndex(['anulado']);
            });
        } catch (\Exception $e) {
            // Ignora indice inexistente
        }

        try {
            Schema::table('pacotes', function (Blueprint $table) {
                $table->dropIndex(['anulado', 'localizacao_atual']);
            });
        } catch (\Exception $e) {
            // Ignora indice inexistente
        }

        Schema::table('pacotes', function (Blueprint $table) {
            $table->dropColumn([
                'anulado',
                'data_anulacao',
                'motivo_anulacao',
                'usuario_anulacao_id'
            ]);
        });
    }
};