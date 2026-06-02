<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdemServico extends Model
{
    use HasFactory;

    const STATUS_ABERTA       = 'aberta';
    const STATUS_EM_ANDAMENTO = 'em_andamento';
    const STATUS_AGUARDANDO   = 'aguardando_peca';
    const STATUS_CONCLUIDA    = 'concluida';
    const STATUS_CANCELADA    = 'cancelada';

    const PRIORIDADE_BAIXA    = 'baixa';
    const PRIORIDADE_MEDIA    = 'media';
    const PRIORIDADE_ALTA     = 'alta';
    const PRIORIDADE_URGENTE  = 'urgente';

    protected $table = 'ordens_servico';

    protected $fillable = [
        'numero', 'cliente_id', 'tecnico_id',
        'equipamento', 'marca', 'modelo', 'numero_serie',
        'descricao_problema', 'diagnostico', 'solucao',
        'status', 'prioridade',
        'data_abertura', 'data_previsao', 'data_conclusao',
        'motivo_cancelamento', 'valor_servico',
    ];

    protected $casts = [
        'data_abertura'  => 'datetime',
        'data_previsao'  => 'datetime',
        'data_conclusao' => 'datetime',
        'valor_servico'  => 'decimal:2',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function tecnico()
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }

    public function historico()
    {
        return $this->hasMany(HistoricoOrdem::class, 'ordem_id')->orderBy('created_at', 'desc');
    }

    public function pecas()
    {
        return $this->hasMany(OrdemPeca::class, 'ordem_id');
    }

    public function isConcluida(): bool
    {
        return $this->status === self::STATUS_CONCLUIDA;
    }

    public function isCancelada(): bool
    {
        return $this->status === self::STATUS_CANCELADA;
    }

    public function tempoAtendimentoHoras(): ?float
    {
        if (!$this->data_conclusao) return null;
        return round($this->data_abertura->diffInMinutes($this->data_conclusao) / 60, 2);
    }

    protected static function booted()
    {
        static::creating(function ($ordem) {
            if (!$ordem->numero) {
                $ordem->numero = 'OS-' . str_pad(
                    (OrdemServico::max('id') ?? 0) + 1,
                    6, '0', STR_PAD_LEFT
                );
            }
        });
    }
}
