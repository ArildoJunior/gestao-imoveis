<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; // Adicione esta linha

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'ArildoJr', // Nome mais descritivo
            'email' => 'arildoajunior@gmail.com', // Email mais descritivo
            'password' => Hash::make('253004jp'), // Garante que a senha seja '253004jp'
            'perfil' => 'ADMINISTRADOR', // CORRIGIDO: de 'ADMIN' para 'ADMINISTRADOR'
            'status' => 'ATIVO', // Adicionado para garantir que o status também seja válido
        ]);

    }
}