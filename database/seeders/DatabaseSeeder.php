<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Field;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Esegui il seeder per i client di Passport
        $this->call(PassportSeeder::class);

        // 2. Esegui il seeder per ruoli e permessi
        $this->call(RolesAndPermissionsSeeder::class);

        // 3. Crea gli utenti e assegna i ruoli
        // Crea l'utente Admin
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);
        $admin->assignRole('Admin');

        // Crea l'utente normale
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);
        $user->assignRole('User');

        // Crea i campi
        Field::factory(5)->create();
    }
}
