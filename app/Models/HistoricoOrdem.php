<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoricoOrdem extends Model
{
    protected $table = 'historico_ordens';

    protected $fillable = [
        'ordem_id', 'usuario_id',
        'status_anterior', 'status_novo',
        'observacao',
    ];

    public function ordem()
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
