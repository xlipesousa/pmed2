<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MotivosGlosaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();
        
        $motivos = [
            [
                'nome' => 'Cobrança indevida',
                'descricao' => 'Item cobrado indevidamente ou em duplicidade'
            ],
            [
                'nome' => 'Documentação incompleta',
                'descricao' => 'Falta de documentação comprobatória necessária'
            ],
            [
                'nome' => 'Procedimento não autorizado',
                'descricao' => 'Procedimento realizado sem autorização prévia necessária'
            ],
            [
                'nome' => 'Valores em desacordo',
                'descricao' => 'Cobranças com valores diferentes da tabela contratual'
            ],
            [
                'nome' => 'Tempo de internação excedido',
                'descricao' => 'Permanência hospitalar além do tempo autorizado'
            ],
            [
                'nome' => 'Procedimento não coberto pelo convênio',
                'descricao' => 'Procedimento não previsto na cobertura contratual'
            ]
        ];
        
        foreach ($motivos as $motivo) {
            DB::table('motivos_glosa')->insert([
                'nome' => $motivo['nome'],
                'descricao' => $motivo['descricao'],
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }
    }
}