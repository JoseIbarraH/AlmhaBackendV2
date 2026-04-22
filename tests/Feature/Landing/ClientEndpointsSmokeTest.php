<?php

declare(strict_types=1);

namespace Tests\Feature\Landing;

use Tests\TestCase;

/**
 * Smoke tests: each public /api/client/* endpoint should respond 200 with the
 * envelope shape expected by the frontend ({ success, message, data }) even
 * when the database is empty.
 */
final class ClientEndpointsSmokeTest extends TestCase
{
    public function test_maintenance_endpoint_returns_envelope(): void
    {
        $this->getJson('/api/client/maintenance')
            ->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data' => ['key', 'value']]);
    }

    public function test_blog_list_returns_envelope_and_empty_pagination(): void
    {
        $this->getJson('/api/client/blog')
            ->assertStatus(200)
            ->assertJsonStructure([
                'success', 'message',
                'data' => ['filters', 'pagination' => ['data', 'current_page', 'total'], 'categories', 'last_three'],
            ])
            ->assertJsonPath('data.pagination.total', 0);
    }

    public function test_blog_detail_unknown_slug_returns_404(): void
    {
        $this->getJson('/api/client/blog/non-existent-slug')
            ->assertStatus(404)
            ->assertJson(['success' => false]);
    }

    public function test_procedure_list_returns_envelope(): void
    {
        $this->getJson('/api/client/procedure')
            ->assertStatus(200)
            ->assertJsonStructure([
                'success', 'message',
                'data' => ['filters', 'pagination' => ['data', 'current_page', 'total'], 'categories'],
            ])
            ->assertJsonPath('data.pagination.total', 0);
    }

    public function test_procedure_detail_unknown_slug_returns_404(): void
    {
        $this->getJson('/api/client/procedure/non-existent-slug')
            ->assertStatus(404);
    }

    public function test_members_list_returns_envelope(): void
    {
        $this->getJson('/api/client/members')
            ->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data'])
            ->assertJsonPath('data', []);
    }

    public function test_member_detail_unknown_slug_returns_404(): void
    {
        $this->getJson('/api/client/members/unknown')
            ->assertStatus(404);
    }

    public function test_contact_data_returns_settings_and_procedures(): void
    {
        $this->getJson('/api/client/contact-data?lang=es')
            ->assertStatus(200)
            ->assertJsonStructure([
                'success', 'message',
                'data' => ['settings' => ['phone', 'email', 'location'], 'procedures'],
            ]);
    }

    public function test_navbar_data_returns_expected_keys(): void
    {
        $this->getJson('/api/client/navbar-data')
            ->assertStatus(200)
            ->assertJsonStructure([
                'success', 'message',
                'data' => ['carousel', 'procedures', 'topProcedure', 'settings' => ['social', 'contact']],
            ]);
    }

    public function test_home_returns_expected_section_shape(): void
    {
        $this->getJson('/api/client/home')
            ->assertStatus(200)
            ->assertJsonStructure([
                'success', 'message',
                'data' => [
                    'backgrounds' => ['background1', 'background1Setting'],
                    'carousel'    => ['carousel', 'carouselSetting'],
                    'carouselTool'=> ['carouselTool', 'carouselToolSetting'],
                    'imageVideo'  => ['imageVideo', 'imageVideoSetting'],
                    'treatments',
                ],
            ]);
    }

    public function test_subscribe_alias_exists_and_validates_email(): void
    {
        $this->postJson('/api/client/subscribe', ['email' => 'not-an-email'])
            ->assertStatus(422);
    }
}
