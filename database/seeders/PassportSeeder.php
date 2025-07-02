<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PassportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pulisce la tabella prima di inserirli per evitare duplicati
        DB::table('oauth_clients')->truncate();

        // Crea il Personal Access Client
        DB::table('oauth_clients')->insert([
            'id' => env('PASSPORT_PERSONAL_ACCESS_CLIENT_ID'),
            'name' => 'Personal Access Client',
            'secret' => env('PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET'), // Inserisce il secret così com'è dal .env
            'provider' => 'users',
            'redirect_uris' => '[]',
            'grant_types' => '["personal_access"]', // <-- CORREZIONE CHIAVE
            'revoked' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Crea il Password Grant Client
        DB::table('oauth_clients')->insert([
            'id' => env('PASSPORT_PASSWORD_GRANT_CLIENT_ID'),
            'name' => 'Password Grant Client',
            'secret' => env('PASSPORT_PASSWORD_GRANT_CLIENT_SECRET'), // Inserisce il secret così com'è dal .env
            'provider' => 'users',
            'redirect_uris' => '[]',
            'grant_types' => '["password", "refresh_token"]', // <-- CORREZIONE CHIAVE
            'revoked' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
