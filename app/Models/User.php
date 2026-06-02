<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    const PERFIL_ADMIN     = 'admin';
    const PERFIL_TECNICO   = 'tecnico';
    const PERFIL_ATENDENTE = 'atendente';

    protected $fillable = ['name', 'email', 'password', 'perfil', 'ativo'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'ativo'             => 'boolean',
        ];
    }

    public function ordensComoTecnico()
    {
        return $this->hasMany(OrdemServico::class, 'tecnico_id');
    }

    public function historicoOrdens()
    {
        return $this->hasMany(HistoricoOrdem::class, 'usuario_id');
    }

    public function isTecnico(): bool
    {
        return $this->perfil === self::PERFIL_TECNICO;
    }

    public function isAdmin(): bool
    {
        return $this->perfil === self::PERFIL_ADMIN;
    }

    public function ordensAtivasCount(): int
    {
        return $this->ordensComoTecnico()
            ->whereIn('status', ['aberta', 'em_andamento'])
            ->count();
    }
}
