<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\UserRole;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        

        // Criar usuário Admin
        User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@iffar.edu.br',
            'role' => UserRole::ADMIN,
        ]);

        // Criar usuário Coordenador
        User::factory()->create([
            'name' => 'Coordenador',
            'email' => 'coordenador@iffar.edu.br',
            'role' => UserRole::COORDENADOR,
        ]);

        // Criar usuário Orientador
        User::factory()->create([
            'name' => 'Orientador',
            'email' => 'orientador@iffar.edu.br',
            'role' => UserRole::ORIENTADOR,
        ]);
    }
}
