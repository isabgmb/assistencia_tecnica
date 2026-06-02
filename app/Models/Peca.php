<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peca extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo', 'nome', 'descricao',
        'estoque', 'estoque_minimo',
        'preco_custo', 'preco_venda', 'ativo',
    ];

    protected $casts = [
        'estoque'         => 'integer',
        'estoque_minimo'  => 'integer',
        'preco_custo'     => 'decimal:2',
        'preco_venda'     => 'decimal:2',
        'ativo'           => 'boolean',
    ];

    public function ordens()
    {
        return $this->hasMany(OrdemPeca::class, 'peca_id');
    }

    public function movimentacoes()
    {
        return $this->hasMany(MovimentacaoEstoque::class, 'peca_id');
    }

    public function temEstoque(int $qtd = 1): bool
    {
        return $this->estoque >= $qtd;
    }
}
