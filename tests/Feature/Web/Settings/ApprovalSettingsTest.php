<?php

namespace Tests\Feature\Web\Settings;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Setting;
use Tests\TestCase;

class ApprovalSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed');
    }

    
    public function test_enable_two_factor()
    {
        Setting::set('approval.enabled', false);

        $this->assertFalse(Setting::get('approval.enabled'));

        $this->actingAsAdmin()
            ->from('/settings/auth')
            ->post('/settings/auth/approval/enable')
            ->assertRedirect('/settings/auth');

        $this->assertTrue(Setting::get('approval.enabled'));
    }

    
    public function test_disable_two_factor()
    {
        Setting::set('approval.enabled', true);

        $this->assertTrue(Setting::get('approval.enabled'));

        $this->actingAsAdmin()
            ->from('/settings/auth')
            ->post('/settings/auth/approval/disable')
            ->assertRedirect('/settings/auth');

        $this->assertFalse(Setting::get('approval.enabled'));
    }
}
