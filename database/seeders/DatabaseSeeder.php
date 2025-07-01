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
        // Esegui il seeder per ruoli e permessi
        $this->call(RolesAndPermissionsSeeder::class);

        // Crea l'utente Admin e assegna il ruolo 'Admin'
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('Admin');


        // Crea l'utente di test e assegna il ruolo 'User'
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole('User');


        Field::factory(5)->create();
    }
}
