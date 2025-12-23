<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TiposPacoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();
        
        $tiposPacote = [
            [
                'nome' => 'Consulta',
                'descricao' => 'Atendimento médico para diagnóstico ou acompanhamento'
            ],
            [
                'nome' => 'Exame',
                'descricao' => 'Procedimentos diagnósticos de laboratório ou imagem'
            ],
            [
                'nome' => 'Internação',
                'descricao' => 'Internação hospitalar para tratamento ou procedimento'
            ],
            [
                'nome' => 'Óbito',
                'descricao' => 'Procedimentos relacionados ao atendimento de óbito'
            ]
        ];
        
        foreach ($tiposPacote as $tipo) {
            DB::table('tipos_pacote')->insert([
                'nome' => $tipo['nome'],
                'descricao' => $tipo['descricao'],
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }
    }
}