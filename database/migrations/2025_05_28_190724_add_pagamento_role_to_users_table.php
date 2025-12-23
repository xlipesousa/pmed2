<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primeiro: descubra os valores atuais do ENUM
        $enumColumns = DB::select("SHOW COLUMNS FROM users WHERE Field = 'role'");
        $enumStr = $enumColumns[0]->Type;
        
        // Extrai os valores do ENUM (formato: enum('valor1','valor2'))
        preg_match("/^enum\(\'(.*)\'\)$/", $enumStr, $matches);
        $enumValues = explode("','", $matches[1]);
        
        // Se 'pagamento' já não existir, adicione-o
        if (!in_array('pagamento', $enumValues)) {
            $enumValues[] = 'pagamento';
            $newEnumStr = "'" . implode("','", $enumValues) . "'";
            
            // Modifique a coluna role para incluir o novo valor
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM($newEnumStr)");
        }

        // Agora adicione o usuário com o papel pagamento
        DB::table('users')->insert([
            'name' => 'Usuário Pagamento',
            'email' => 'pagamento@4rm.eb.mil.br',
            'password' => Hash::make('senha123'),
            'role' => 'pagamento',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover o usuário
        DB::table('users')->where('email', 'pagamento@4rm.eb.mil.br')->delete();
        
        // Aqui poderíamos também reverter a modificação no ENUM,
        // mas isso não é recomendado se podem existir outros usuários com esse papel
    }
};
