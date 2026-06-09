<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
            'email' => 'admin@teste.com',
            'role' => UserRole::ADMIN,
            'password' => Hash::make('123'),
        ]);

        // Criar usuário Coordenador
        User::factory()->create([
            'name' => 'Coordenador',
            'email' => 'coordenador@teste.com',
            'role' => UserRole::COORDENADOR,
            'password' => Hash::make('123'),
        ]);

        // Criar usuário Orientador
        User::factory()->create([
            'name' => 'Orientador',
            'email' => 'orientador@teste.com',
            'role' => UserRole::ORIENTADOR,
            'password' => Hash::make('123'),
        ]);
    }
}
