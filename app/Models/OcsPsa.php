<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OcsPsa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ocs_psa';
    
    protected $fillable = [
        'nome',
        'codigo_interno',
        'ativo'
    ];
    
    protected $casts = [
        'ativo' => 'boolean',
    ];
    
    public function pacotes()
    {
        return $this->hasMany(Pacote::class, 'ocs_psa_id');
    }
}