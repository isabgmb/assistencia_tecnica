<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome', 'cpf', 'telefone', 'email',
        'cep', 'logradouro', 'numero', 'complemento',
        'bairro', 'cidade', 'estado', 'ativo',
    ];

    protected $casts = ['ativo' => 'boolean'];

    public function ordens()
    {
        return $this->hasMany(OrdemServico::class);
    }
}
