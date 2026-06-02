<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ordens_servico', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->foreignId('cliente_id')->constrained('clientes')->restrictOnDelete();
            $table->foreignId('tecnico_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('equipamento');
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->string('numero_serie')->nullable();
            $table->text('descricao_problema');
            $table->text('diagnostico')->nullable();
            $table->text('solucao')->nullable();
            $table->enum('status', ['aberta', 'em_andamento', 'aguardando_peca', 'concluida', 'cancelada'])
                  ->default('aberta');
            $table->enum('prioridade', ['baixa', 'media', 'alta', 'urgente'])->default('media');
            $table->timestamp('data_abertura');
            $table->timestamp('data_previsao')->nullable();
            $table->timestamp('data_conclusao')->nullable();
            $table->text('motivo_cancelamento')->nullable();
            $table->decimal('valor_servico', 10, 2)->nullable();
            $table->timestamps();

            $table->index(['status', 'prioridade']);
            $table->index('data_previsao');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordens_servico');
    }
};
