<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdemPeca extends Model
{
    protected $table = 'ordem_pecas';

    protected $fillable = [
        'ordem_id', 'peca_id', 'quantidade', 'preco_unitario',
    ];

    protected $casts = [
        'quantidade'      => 'integer',
        'preco_unitario'  => 'decimal:2',
    ];

    public function ordem()
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_id');
    }

    public function peca()
    {
        return $this->belongsTo(Peca::class, 'peca_id');
    }
}
