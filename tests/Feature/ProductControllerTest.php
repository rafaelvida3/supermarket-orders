<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProductControllerTest extends TestCase
{

    /**
     * Tests whether the product listing endpoint returns a successful response.
     *
     * This test performs a GET request to /api/produtos
     * and asserts that the HTTP status code is 200 (OK)
     * and that the response header indicates JSON content.
     */
    #[Test]
    public function test_it_returns_a_successful_response_for_products_list(): void
    {
        // Perform a GET request to the /api/produtos route
        $response = $this->get('/api/produtos');

        // Assert that the HTTP status code is 200 (success)
        $response->assertStatus(200)

                 // Assert that the Content-Type header contains "application/json"
                 ->assertHeader('Content-Type', 'application/json');
    }
}
