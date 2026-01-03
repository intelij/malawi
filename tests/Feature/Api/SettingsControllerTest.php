<?php

namespace Tests\Feature\Api;

use Facades\Tests\Setup\UserFactory;
use Setting;
use Tests\Feature\ApiTestCase;
use App\Models\User;

class SettingsControllerTest extends ApiTestCase
{
    
    public function test_only_authenticated_users_can_view_app_settings()
    {
        $this->getJson('/api/settings')->assertStatus(401);
    }

    
    public function test_get_settings_without_permission()
    {
        $user = User::factory()->create();

        $this->actingAs($user, self::API_GUARD)
            ->getJson('/api/settings')
            ->assertStatus(403);
    }

    
    public function test_get_settings()
    {
        $user = UserFactory::withPermissions('settings.general')->create();

        $this->actingAs($user, self::API_GUARD)
            ->getJson('/api/settings')
            ->assertOk()
            ->assertJson(Setting::all());
    }
}
