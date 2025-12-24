<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Verificar se o usuário admin já existe
        $existingAdmin = User::where('email', 'admin@admin')->first();
        
        if (!$existingAdmin) {
            User::create([
                'name' => 'Administrador',
                'email' => 'admin@admin',
                'password' => Hash::make('admin'),
                'role' => 'admin',
            ]);
            
            $this->command->info('Usuário administrador criado com sucesso!');
            $this->command->info('Email: admin@admin | Senha: admin');
        } else {
            $this->command->info('Usuário administrador já existe.');
        }
    }
}