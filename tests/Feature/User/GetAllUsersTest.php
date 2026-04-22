<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use Tests\TestCase;

class GetAllUsersTest extends TestCase
{
    private const ENDPOINT = '/api/v1/users';

    public function test_returns_paginated_users(): void
    {
        $this->createUser();
        $this->createUser();

        $response = $this->actingAsAdmin()->getJson(self::ENDPOINT);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);
    }

    public function test_returns_401_when_unauthenticated(): void
    {
        $response = $this->getJson(self::ENDPOINT);

        $response->assertStatus(401);
    }

    public function test_clamps_per_page_to_100(): void
    {
        $response = $this->actingAsAdmin()->getJson(self::ENDPOINT . '?per_page=9999');

        $response->assertStatus(200);
        $this->assertLessThanOrEqual(100, $response->json('meta.per_page'));
    }

    public function test_defaults_to_page_1_when_page_is_negative(): void
    {
        $response = $this->actingAsAdmin()->getJson(self::ENDPOINT . '?page=-5');

        $response->assertStatus(200);
        $this->assertSame(1, $response->json('meta.current_page'));
    }
}
