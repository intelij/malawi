<?php

namespace Tests\Feature\Web\Settings;

use Facades\Tests\Setup\RoleFactory;
use Facades\Tests\Setup\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Setting;
use Tests\TestCase;

class AuthSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed');
    }


    public function test_update_auth_settings()
    {
        Setting::set('app_name', 'bar');

        $data = $this->getAuthSettingsData();

        $this->actingAsAdmin()
            ->from('/settings/auth')
            ->post('/settings/auth', $data)
            ->assertRedirect('/settings/auth');

        $this->assertAuthSettingsUpdated($data);
    }


    public function test_only_users_with_appropriate_permission_can_update_auth_settings()
    {
        Setting::set('app_name', 'foo');

        $roleA = RoleFactory::create();
        $roleB = RoleFactory::withPermissions('settings.auth')->create();

        $userA = UserFactory::role($roleA)->create();
        $userB = UserFactory::role($roleB)->create();

        $data = $this->getAuthSettingsData();

        $data["app_name"] = "bar";

        $this->actingAs($userA)
            ->from('/settings/auth')
            ->post('/settings/auth', $data)
            ->assertStatus(403);

        $this->assertNotEquals("bar", (string) Setting::get('app_name'));

        $this->actingAs($userB)
            ->from('/settings/auth')
            ->post('/settings/auth', $data)
            ->assertRedirect('/settings/auth');

        $this->assertEquals("bar", (string) Setting::get('app_name'));
    }

    private function assertAuthSettingsUpdated(array $data)
    {
        $this->assertEquals((string) $data['remember_me'], (string) Setting::get('remember_me'));
        $this->assertEquals((string) $data['forgot_password'], (string) Setting::get('forgot_password'));
        $this->assertEquals((string) $data['login_reset_token_lifetime'], (string) Setting::get('login_reset_token_lifetime'));
        $this->assertEquals((string) $data['throttle_enabled'], (string) Setting::get('throttle_enabled'));
        $this->assertEquals((string) $data['throttle_attempts'], (string) Setting::get('throttle_attempts'));
        $this->assertEquals((string) $data['throttle_lockout_time'], (string) Setting::get('throttle_lockout_time'));
        $this->assertEquals((string) $data['reg_enabled'], (string) Setting::get('reg_enabled'));
        $this->assertEquals((string) $data['tos'], (string) Setting::get('tos'));
        $this->assertEquals((string) $data['reg_email_confirmation'], (string) Setting::get('reg_email_confirmation'));
    }

    private function getAuthSettingsData(): array
    {
        return [
            'remember_me' => 1,
            'forgot_password' => 1,
            'login_reset_token_lifetime' => 123,
            'throttle_enabled' => 1,
            'throttle_attempts' => 10,
            'throttle_lockout_time' => 2,
            'reg_enabled' => 1,
            'tos' => 1,
            'reg_email_confirmation' => 1,
        ];
    }
}
