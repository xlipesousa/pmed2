<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EquipeUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Lista de usuários por equipe
        $equipeUsers = [
            [
                'name' => 'Usuário Protocolo',
                'email' => 'protocolo@4rm.eb.mil.br',
                'password' => Hash::make('senha123'),
                'role' => 'protocolo',
            ],
            [
                'name' => 'Usuário Lisura',
                'email' => 'lisura@4rm.eb.mil.br',
                'password' => Hash::make('senha123'),
                'role' => 'lisura',
            ],
            [
                'name' => 'Usuário SIRE',
                'email' => 'sire@4rm.eb.mil.br',
                'password' => Hash::make('senha123'),
                'role' => 'sire',
            ],
            [
                'name' => 'Usuário Glosa',
                'email' => 'glosa@4rm.eb.mil.br',
                'password' => Hash::make('senha123'),
                'role' => 'glosa',
            ],
            [
                'name' => 'Usuário Arquivo',
                'email' => 'arquivo@4rm.eb.mil.br',
                'password' => Hash::make('senha123'),
                'role' => 'arquivo',
            ],
        ];
        
        // Criar usuários se não existirem
        foreach ($equipeUsers as $userData) {
            $existingUser = User::where('email', $userData['email'])->first();
            
            if (!$existingUser) {
                User::create($userData);
                $this->command->info("Usuário {$userData['email']} criado com sucesso!");
            } else {
                $this->command->info("Usuário {$userData['email']} já existe. Pulando criação.");
            }
        }
    }
}