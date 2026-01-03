<?php

namespace Tests\Feature\Api\Users;

use Facades\Tests\Setup\UserFactory;
use Illuminate\Http\UploadedFile;
use Storage;
use Tests\Feature\ApiTestCase;
use App\Events\User\UpdatedByAdmin;
use App\Models\User;

class AvatarControllerTest extends ApiTestCase
{

    public function test_upload_user_avatar_unauthenticated()
    {
        $user = User::factory()->create();

        $this->post("/api/users/{$user->id}/avatar")
            ->assertStatus(401);
    }


    public function test_upload_avatar_without_permission()
    {
        $user = User::factory()->create();

        $this->actingAs($user, self::API_GUARD)
            ->post("/api/users/{$user->id}/avatar")
            ->assertForbidden();
    }


    public function test_upload_avatar_image()
    {
        \Event::fake([UpdatedByAdmin::class]);

        Storage::fake('public');

        $user = UserFactory::withPermissions('users.manage')->create();

        $file = UploadedFile::fake()->image('avatar.png', 500, 500);

        $response = $this->actingAs($user, self::API_GUARD)
            ->post("/api/users/{$user->id}/avatar", ['file' => $file])
            ->assertOk();

        $avatar = $response->json('data.avatar');
        $this->assertNotNull($avatar);

        $uploadedFile = str_replace(url(''), '', $avatar);
        $uploadedFile = ltrim($uploadedFile, '/storage/');

        Storage::disk('public')->assertExists($uploadedFile);

        [$width, $height] = getimagesizefromstring(
            Storage::disk('public')->get($uploadedFile)
        );

        $this->assertEquals(160, $width);
        $this->assertEquals(160, $height);

        \Event::assertDispatched(UpdatedByAdmin::class);
    }


    public function test_upload_invalid_image()
    {
        $user = UserFactory::withPermissions('users.manage')->create();

        Storage::fake('public');

        $file = UploadedFile::fake()->create('avatar.txt', 500);

        $this->actingAs($user, self::API_GUARD)
            ->post("/api/users/{$user->id}/avatar", ['file' => $file])
            ->assertStatus(422)
            ->assertJsonFragment([
                'file' => [
                    trans('validation.image', ['attribute' => 'file']),
                ],
            ]);
    }


    public function test_update_avatar_from_external_source()
    {
        \Event::fake([UpdatedByAdmin::class]);

        $user = UserFactory::withPermissions('users.manage')->create();

        $url = 'http://google.com';

        $this->actingAs($user, self::API_GUARD)
            ->putJson("/api/users/{$user->id}/avatar/external", ['url' => $url])
            ->assertOk()
            ->assertJsonFragment(['avatar' => $url]);

        \Event::assertDispatched(UpdatedByAdmin::class);
    }


    public function test_update_avatar_with_invalid_external_source()
    {
        $user = UserFactory::withPermissions('users.manage')->create();

        $this->actingAs($user, self::API_GUARD)
            ->putJson("/api/users/{$user->id}/avatar/external", ['url' => 'foo'])
            ->assertStatus(422);
    }


    public function test_delete_user_avatar()
    {
        \Event::fake([UpdatedByAdmin::class]);

        $user = UserFactory::withPermissions('users.manage')->create();

        $user->forceFill(['avatar' => 'http://google.com'])->save();

        $this->actingAs($user, self::API_GUARD)
            ->deleteJson("api/users/{$user->id}/avatar")
            ->assertOk()
            ->assertJsonFragment([
                'avatar' => url('assets/img/profile.png'), // default profile image
            ]);

        \Event::assertDispatched(UpdatedByAdmin::class);
    }
}
