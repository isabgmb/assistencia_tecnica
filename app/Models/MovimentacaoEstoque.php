<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimentacaoEstoque extends Model
{
    protected $table = 'movimentacoes_estoque';

    protected $fillable = [
        'peca_id', 'usuario_id', 'ordem_id',
        'tipo', 'quantidade', 'estoque_anterior',
        'estoque_posterior', 'observacao',
    ];

    protected $casts = ['quantidade' => 'integer'];

    public function peca()    { return $this->belongsTo(Peca::class, 'peca_id'); }
    public function usuario() { return $this->belongsTo(User::class, 'usuario_id'); }
    public function ordem()   { return $this->belongsTo(OrdemServico::class, 'ordem_id'); }
}
