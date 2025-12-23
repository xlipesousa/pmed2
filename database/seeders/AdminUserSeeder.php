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
        $existingAdmin = User::where('email', 'admin@pmed2.com')->first();
        
        if (!$existingAdmin) {
            User::create([
                'name' => 'Administrador',
                'email' => 'admin@pmed2.com',
                'password' => Hash::make('admin'),
                'role' => 'admin',
            ]);
            
            $this->command->info('Usuário administrador criado com sucesso!');
        } else {
            $this->command->info('Usuário administrador já existe.');
        }
    }
}