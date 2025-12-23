<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'active' => 'boolean',
    ];

    /**
     * Verifica se o usuário tem um determinado papel
     *
     * @param string $role
     * @return bool
     */
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    /**
     * Verifica se o usuário é um administrador
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function movimentacoes()
    {
        return $this->hasMany(Movimentacao::class, 'user_id');
    }

    /**
     * Retorna a data do último login, se existir
     * 
     * @return null|\Carbon\Carbon
     */
    public function getLastLoginAttribute()
    {
        // Retorna null para não quebrar as views se o campo não existir
        return null;
    }

    /**
     * Define a URL para o perfil do usuário no AdminLTE.
     *
     * @return string
     */
    public function adminlte_profile_url()
    {
        // Retorna a URL para a rota de perfil
        return route('perfil');
    }
}
