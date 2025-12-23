<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TiposContaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();
        
        $tiposConta = [
            [
                'nome' => 'Ambulatório',
                'descricao' => 'Atendimento em nível ambulatorial sem internação'
            ],
            [
                'nome' => 'Home Care',
                'descricao' => 'Serviços de atendimento domiciliar'
            ],
            [
                'nome' => 'Honorário',
                'descricao' => 'Pagamentos de honorários médicos e profissionais'
            ],
            [
                'nome' => 'Internação',
                'descricao' => 'Custos referentes a internações hospitalares'
            ],
            [
                'nome' => 'Laboratório',
                'descricao' => 'Exames laboratoriais e diagnósticos'
            ],
            [
                'nome' => 'Oncologia',
                'descricao' => 'Tratamentos específicos para câncer'
            ],
            [
                'nome' => 'PA',
                'descricao' => 'Atendimentos em pronto-atendimento'
            ],
            [
                'nome' => 'Reabilitação',
                'descricao' => 'Tratamentos de fisioterapia e reabilitação'
            ],
            [
                'nome' => 'Remoção',
                'descricao' => 'Serviços de transporte de pacientes'
            ],
            [
                'nome' => 'TRS - (Hemodiálise)',
                'descricao' => 'Terapia Renal Substitutiva e procedimentos de hemodiálise'
            ],
            [
                'nome' => 'Recurso de Glosa',
                'descricao' => 'Valores referentes a recursos de glosa'
            ]
        ];
        
        foreach ($tiposConta as $tipo) {
            DB::table('tipos_conta')->insert([
                'nome' => $tipo['nome'],
                'descricao' => $tipo['descricao'],
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }
    }
}