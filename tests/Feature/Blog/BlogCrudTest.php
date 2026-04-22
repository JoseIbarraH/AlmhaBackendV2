<?php

declare(strict_types=1);

namespace Tests\Feature\Blog;

use Tests\TestCase;

class BlogCrudTest extends TestCase
{
    private function createBlogCategory(): string
    {
        \Spatie\Permission\Models\Permission::firstOrCreate(
            ['name' => 'create_blogs', 'guard_name' => 'api']
        );

        \DB::table('blog_categories')->insertOrIgnore([
            'code' => 'TEST_CAT',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return 'TEST_CAT';
    }

    public function test_returns_401_on_unauthenticated_list(): void
    {
        $response = $this->getJson('/api/v1/blogs');

        $response->assertStatus(401);
    }

    public function test_returns_paginated_blogs(): void
    {
        $response = $this->actingAsAdmin()->getJson('/api/v1/blogs');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_returns_422_when_creating_blog_without_required_fields(): void
    {
        $response = $this->actingAsAdmin()->postJson('/api/v1/blogs', []);

        $response->assertStatus(422)
            ->assertJsonPath('error', 'validation_error');
    }

    public function test_returns_404_on_nonexistent_blog(): void
    {
        $response = $this->actingAsAdmin()->getJson('/api/v1/blogs/99999');

        $response->assertStatus(404);
    }

    public function test_returns_404_on_delete_nonexistent_blog(): void
    {
        $response = $this->actingAsAdmin()->deleteJson('/api/v1/blogs/99999');

        $response->assertStatus(404);
    }
}
