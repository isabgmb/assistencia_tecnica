<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome'     => $this->faker->name(),
            'cpf'      => $this->faker->unique()->numerify('###.###.###-##'),
            'telefone' => $this->faker->phoneNumber(),
            'email'    => $this->faker->unique()->safeEmail(),
            'cep'      => '01310-100',
            'cidade'   => 'São Paulo',
            'estado'   => 'SP',
            'ativo'    => true,
        ];
    }
}
