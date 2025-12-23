<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Adiciona o AdminUserSeeder como primeiro seeder a ser executado
        $this->call([
            AdminUserSeeder::class,
            EquipeUsersSeeder::class, // Adicionar esta linha
            MotivosGlosaSeeder::class,
            OcsPsaSeeder::class,
            TiposPacoteSeeder::class,
            TiposContaSeeder::class,
        ]);
    }
}
