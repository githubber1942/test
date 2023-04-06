<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class RouteTest extends TestCase
{
    /**
     * Testing the home route.
     *
     * @return void
     */
    public function test_home_route()
    {
        $response = $this->get('/home');
        $response->assertViewIs('home');
    }

    /**
     * Testing the create-subscriber route.
     *
     * @return void
     */
    public function test_create_subscriber_route()
    {
        $response = $this->get('/create-subscriber');
        $response->assertViewIs('create-subscriber');
    }

    /**
     * Testing the list subscribers route.
     *
     * @return void
     */
    public function test_list_subscribers_route()
    {
        $response = $this->get('/newsubscribers');
        $response->assertViewIs('newsubscribers');
    }
}
