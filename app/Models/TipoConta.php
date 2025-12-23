<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoConta extends Model
{
    use HasFactory;

    protected $table = 'tipos_conta';
    
    protected $fillable = ['nome', 'descricao'];
}