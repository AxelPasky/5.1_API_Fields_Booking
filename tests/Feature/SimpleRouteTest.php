<?php

namespace Tests\Feature;

use Tests\TestCase;

class SimpleRouteTest extends TestCase
{
     #[Test]
    public function the_application_returns_a_successful_response()
    {
        // Questo test non usa il database.
        // Controlla solo che l'applicazione risponda sulla rotta principale.
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
