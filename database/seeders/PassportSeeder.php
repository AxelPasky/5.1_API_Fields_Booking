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
        
        DB::table('oauth_clients')->truncate();

       
        DB::table('oauth_clients')->insert([
            'id' => env('PASSPORT_PERSONAL_ACCESS_CLIENT_ID'),
            'name' => 'Personal Access Client',
            'secret' => 'dummy-secret-for-personal-client', 
            'provider' => 'users',
            'redirect_uris' => '[]',
            'grant_types' => '["personal_access"]',
            'revoked' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        
        DB::table('oauth_clients')->insert([
            'id' => env('PASSPORT_PASSWORD_GRANT_CLIENT_ID'),
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
