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

     
        $personalClientId = $this->getPersonalClientId();
        $personalClientSecret = $this->getPersonalClientSecret();

        DB::table('oauth_clients')->insert([
            'id' => $personalClientId,
            'name' => 'Personal Access Client',
            'secret' => $personalClientSecret,
            'provider' => 'users',
            'redirect_uris' => '[]',
            'grant_types' => '["personal_access"]',
            'revoked' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        
        if (env('PASSPORT_PASSWORD_GRANT_CLIENT_ID')) {
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

    private function getPersonalClientId(): int|string
    {
     
        if (app()->environment('testing')) {
            return 1;
        }

       
        return env('PASSPORT_PERSONAL_ACCESS_CLIENT_ID', 1);
    }

    private function getPersonalClientSecret(): string
    {
       
        if (app()->environment('testing')) {
            return 'test-secret-for-personal-client';
        }

     
        if (env('PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET')) {
            return env('PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET');
        }

        return 'REPLACE-WITH-ACTUAL-SECRET-FROM-PASSPORT-INSTALL';
    }
}