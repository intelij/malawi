<?php

namespace Tests\Feature\Api\Profile;

use Illuminate\Http\UploadedFile;
use Storage;
use Tests\Feature\ApiTestCase;

class AvatarControllerTest extends ApiTestCase
{

    public function test_only_authenticated_user_can_update_avatar()
    {
        $this->post('/api/me/avatar')->assertStatus(401);
    }


    public function test_upload_avatar_image()
    {
        $this->login();

        Storage::fake('public');

        $file = UploadedFile::fake()->image('avatar.png', 500, 500);

        $response = $this->post('api/me/avatar', [
            'file' => $file,
        ]);

        $this->assertNotNull($response->json('data.avatar'));

        $uploadedFile = str_replace(url(''), '', $response->json('data.avatar'));
        $uploadedFile = ltrim($uploadedFile, '/storage/');

        Storage::disk('public')->assertExists($uploadedFile);

        [$width, $height] = getimagesizefromstring(
            Storage::disk('public')->get($uploadedFile)
        );

        $this->assertEquals(160, $width);
        $this->assertEquals(160, $height);
    }


    public function test_upload_invalid_image()
    {
        $this->login();

        Storage::fake('public');

        $file = UploadedFile::fake()->create('avatar.txt', 500);

        $this->post('/api/me/avatar', ['file' => $file])
            ->assertStatus(422)
            ->assertJsonFragment([
                'file' => [
                    trans('validation.image', ['attribute' => 'file']),
                ],
            ]);
    }


    public function test_update_avatar_from_external_source()
    {
        $this->login();

        $url = 'http://google.com';

        $this->putJson('/api/me/avatar/external', ['url' => $url])
            ->assertOk()
            ->assertJsonFragment(['avatar' => $url]);
    }


    public function test_update_avatar_with_invalid_external_source()
    {
        $this->login();

        $this->putJson('/api/me/avatar/external', ['url' => 'foo'])
            ->assertStatus(422);
    }


    public function test_delete_avatar()
    {
        $user = $this->login();

        $user->forceFill(['avatar' => 'http://google.com'])->save();

        $this->deleteJson('api/me/avatar')
            ->assertOk()
            ->assertJsonFragment([
                'avatar' => url('assets/img/profile.png'), // default profile image
            ]);
    }
}
