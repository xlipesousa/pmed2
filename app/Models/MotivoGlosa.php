<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MotivoGlosa extends Model
{
    use HasFactory;

    protected $table = 'motivos_glosa';
    
    protected $fillable = ['nome', 'descricao'];
}