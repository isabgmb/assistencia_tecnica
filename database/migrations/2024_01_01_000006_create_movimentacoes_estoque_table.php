<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('movimentacoes_estoque', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peca_id')->constrained('pecas')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('ordem_id')->nullable()->constrained('ordens_servico')->nullOnDelete();
            $table->enum('tipo', ['entrada', 'saida', 'ajuste']);
            $table->integer('quantidade');
            $table->integer('estoque_anterior');
            $table->integer('estoque_posterior');
            $table->text('observacao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimentacoes_estoque');
    }
};
