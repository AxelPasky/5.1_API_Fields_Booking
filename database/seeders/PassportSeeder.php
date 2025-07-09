<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PassportSeeder extends Seeder
{
    public function run(): void
    {
        // Svuota la tabella
        DB::table('oauth_clients')->truncate();

        // Personal Access Client con ID numerico
        DB::table('oauth_clients')->insert([
            'id' => 1,  // ID numerico!
            'name' => 'Personal Access Client',
            'secret' => env('PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET'),
            'provider' => 'users',
            'redirect_uris' => '[]',
            'grant_types' => '["personal_access"]',
            'revoked' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Password Grant Client con ID numerico
        DB::table('oauth_clients')->insert([
            'id' => 2,  // ID numerico!
            'name' => 'Password Grant Client',
            'secret' => env('PASSPORT_PASSWORD_GRANT_CLIENT_SECRET'),
            'provider' => 'users',
            'redirect_uris' => '[]',
            'grant_types' => '["password", "refresh_token"]',
            'revoked' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}