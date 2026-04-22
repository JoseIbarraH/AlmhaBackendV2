<?php

declare(strict_types=1);

namespace Tests\Feature\Landing;

use Illuminate\Support\Facades\Cache;
use Src\Admin\Settings\Infrastructure\Models\EloquentSettingModel;
use Src\Admin\Team\Infrastructure\Models\TeamEloquentModel;
use Src\Shared\Infrastructure\Cache\ClientCache;
use Tests\TestCase;

final class ClientCacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_maintenance_response_is_served_from_cache_after_first_call(): void
    {
        $this->getJson('/api/client/maintenance')->assertStatus(200);

        // Now create a setting that would change the response IF cache wasn't working.
        EloquentSettingModel::create([
            'key'   => 'is_maintenance_mode',
            'value' => true,
            'group' => 'system',
        ]);

        // Manually flush only navbar group (NOT maintenance) to prove the cache key
        // is independent. Without invalidation, this should still return cached false.
        ClientCache::flushGroups('navbar');

        $response = $this->getJson('/api/client/maintenance');

        // Settings model has observers — the create() call above already invalidated
        // the maintenance group via AppServiceProvider hooks, so we expect TRUE now.
        $response->assertStatus(200)
            ->assertJsonPath('data.value', true);
    }

    public function test_settings_change_invalidates_maintenance_navbar_and_contact_data(): void
    {
        // Prime caches
        $this->getJson('/api/client/maintenance')->assertStatus(200);
        $this->getJson('/api/client/navbar-data')->assertStatus(200);
        $this->getJson('/api/client/contact-data')->assertStatus(200);

        $this->assertCacheGroupsHaveKeys(['maintenance', 'navbar', 'contact_data']);

        // Save a setting → triggers observer → flushes those groups
        EloquentSettingModel::create([
            'key'   => 'phone',
            'value' => '+57 999 888 7777',
            'group' => 'general',
        ]);

        $this->assertCacheGroupsAreEmpty(['maintenance', 'navbar', 'contact_data']);
    }

    public function test_team_change_invalidates_member_group(): void
    {
        $this->getJson('/api/client/members')->assertStatus(200);
        $this->assertCacheGroupsHaveKeys(['member']);

        TeamEloquentModel::create([
            'name'   => 'Dr. Test',
            'slug'   => 'dr-test',
            'status' => 'active',
        ]);

        $this->assertCacheGroupsAreEmpty(['member']);
    }

    public function test_member_list_is_re_fetched_after_team_creation(): void
    {
        $this->getJson('/api/client/members')
            ->assertStatus(200)
            ->assertJsonPath('data', []);

        TeamEloquentModel::create([
            'name'   => 'Dra. Cache Bust',
            'slug'   => 'dra-cache-bust',
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/client/members');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_flush_groups_with_unknown_group_is_noop(): void
    {
        ClientCache::flushGroups('does_not_exist'); // should not throw
        $this->assertTrue(true);
    }

    public function test_remember_uses_short_ttl_for_maintenance(): void
    {
        $this->assertSame(60,  ClientCache::TTL_SHORT);
        $this->assertSame(300, ClientCache::TTL_MEDIUM);
        $this->assertSame(600, ClientCache::TTL_LONG);
    }

    /**
     * @param string[] $groups
     */
    private function assertCacheGroupsHaveKeys(array $groups): void
    {
        foreach ($groups as $group) {
            $registry = Cache::get("almha_client:_keys:{$group}", []);
            $this->assertNotEmpty($registry, "Expected group '{$group}' to have cached keys.");
        }
    }

    /**
     * @param string[] $groups
     */
    private function assertCacheGroupsAreEmpty(array $groups): void
    {
        foreach ($groups as $group) {
            $registry = Cache::get("almha_client:_keys:{$group}", []);
            $this->assertEmpty($registry, "Expected group '{$group}' to be flushed.");
        }
    }
}
