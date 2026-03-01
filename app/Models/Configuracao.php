<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuracao extends Model
{
    use HasFactory;

    protected $table = 'configuracoes';
    
    protected $fillable = [
        'chave',
        'valor'
    ];

    public static function obterValor(string $chave, $padrao = null)
    {
        $config = self::where('chave', $chave)->first();

        return $config ? $config->valor : $padrao;
    }

    public static function obterValorNumerico(string $chave, float $padrao = 0): float
    {
        $valor = self::obterValor($chave, $padrao);

        return is_numeric($valor) ? (float) $valor : $padrao;
    }

    public static function definirValor(string $chave, $valor): self
    {
        return self::updateOrCreate(
            ['chave' => $chave],
            ['valor' => (string) $valor]
        );
    }
}