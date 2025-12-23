<?php

namespace Database\Factories;

use App\Models\Pacote;
use App\Models\OcsPsa;
use App\Models\TipoPacote;
use App\Models\TipoConta;
use App\Models\MotivoGlosa;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class PacoteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Pacote::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $dataEntrada = $this->faker->dateTimeBetween('-90 days', 'now');
        $valorFatura = $this->faker->randomFloat(2, 1000, 50000);
        $valorGlosa = $this->faker->randomElement([0, $this->faker->randomFloat(2, 0, $valorFatura * 0.3)]);
        $valorPosLisura = $valorFatura - $valorGlosa;
        $valorPago = $this->faker->randomFloat(2, 0, $valorPosLisura);
        
        $localizacoes = ['Protocolo', 'Lisura', 'SIRE', 'Glosa', 'Arquivo'];
        $localizacaoAtual = $this->faker->randomElement($localizacoes);
        
        $status = $this->faker->randomElement(['normal', 'atrasado', 'critico']);
        $estadosGerais = ['Em processamento', 'Aguardando Limite de Crédito', 'Aguardando Recurso de Glosa', 'Finalizado'];
        
        return [
            'ocs_psa_id' => OcsPsa::inRandomOrder()->first()->id ?? $this->faker->numberBetween(1, 5),
            'tipo_id' => TipoPacote::inRandomOrder()->first()->id ?? $this->faker->numberBetween(1, 4),
            'tipo_conta_id' => TipoConta::inRandomOrder()->first()->id ?? $this->faker->numberBetween(1, 3),
            'motivo_glosa_id' => $valorGlosa > 0 ? MotivoGlosa::inRandomOrder()->first()->id ?? $this->faker->numberBetween(1, 5) : null,
            'numero_fatura' => 'F-' . $this->faker->numberBetween(70000, 99999) . '/' . date('Y'),
            'data_entrada' => $dataEntrada,
            'valor_fatura' => $valorFatura,
            'valor_glosa' => $valorGlosa,
            'valor_pos_lisura' => $valorPosLisura,
            'valor_pago' => $valorPago,
            'valor_pendente' => $valorPosLisura - $valorPago,
            'estado_geral' => $this->faker->randomElement($estadosGerais),
            'estado_glosa' => $valorGlosa > 0 ? $this->faker->randomElement(['Notificada', 'Recurso Recebido', 'Recurso Analisado']) : null,
            'localizacao_atual' => $localizacaoAtual,
            'localizacao_anterior' => $this->faker->randomElement(array_diff($localizacoes, [$localizacaoAtual])),
            'ultima_acao' => $this->faker->sentence(4),
            'observacoes' => $this->faker->paragraph,
            'status' => $status,
        ];
    }

    /**
     * Estado para pacotes com glosa
     */
    public function comGlosa()
    {
        return $this->state(function (array $attributes) {
            $valorFatura = $attributes['valor_fatura'] ?? $this->faker->randomFloat(2, 1000, 50000);
            $valorGlosa = $this->faker->randomFloat(2, $valorFatura * 0.1, $valorFatura * 0.3);
            $valorPosLisura = $valorFatura - $valorGlosa;
            
            return [
                'valor_glosa' => $valorGlosa,
                'valor_pos_lisura' => $valorPosLisura,
                'estado_glosa' => $this->faker->randomElement(['Notificada', 'Recurso Recebido', 'Recurso Analisado']),
                'data_notificacao_glosa' => $this->faker->dateTimeBetween('-60 days', '-10 days'),
                'motivo_glosa_id' => MotivoGlosa::inRandomOrder()->first()->id ?? $this->faker->numberBetween(1, 5),
            ];
        });
    }

    /**
     * Estado para pacotes atrasados
     */
    public function atrasado()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'atrasado',
                'data_entrada' => $this->faker->dateTimeBetween('-60 days', '-30 days'),
            ];
        });
    }

    /**
     * Estado para pacotes críticos
     */
    public function critico()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'critico',
                'data_entrada' => $this->faker->dateTimeBetween('-90 days', '-60 days'),
            ];
        });
    }
}