<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Halaman utama mengarahkan tamu ke halaman login.
     */
    public function test_halaman_utama_mengarahkan_tamu_ke_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }
}