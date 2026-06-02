<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ordem_pecas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordem_id')->constrained('ordens_servico')->cascadeOnDelete();
            $table->foreignId('peca_id')->constrained('pecas')->restrictOnDelete();
            $table->integer('quantidade');
            $table->decimal('preco_unitario', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordem_pecas');
    }
};
