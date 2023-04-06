<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class SaveKeyTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_save_key()
    {
        $apiKey = 'test-api-key';
        DB::table('api_keys')->insert(['key' => $apiKey]);

        $response = $this->post('/save-api-key', ['api_key' => $apiKey]);
        $this->assertDatabaseHas('api_keys', ['key' => $apiKey]);

        $response->assertRedirect('/home');
    }
}
