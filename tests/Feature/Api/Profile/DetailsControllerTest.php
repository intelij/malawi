<?php

namespace Tests\Feature\Api\Profile;

use Carbon\Carbon;
use Tests\Feature\ApiTestCase;
use App\Http\Resources\UserResource;

class DetailsControllerTest extends ApiTestCase
{
    
    public function test_get_user_profile_unauthenticated()
    {
        $this->getJson('/api/me')->assertStatus(401);
    }

    
    public function test_get_user_profile()
    {
        $user = $this->login();

        $this->getJson('/api/me')
            ->assertOk()
            ->assertJson(['data' => (new UserResource($user))->resolve()]);
    }

    
    public function test_update_user_profile_unauthenticated()
    {
        $this->patchJson('/api/me/details')->assertStatus(401);
    }

    
    public function test_update_user_profile()
    {
        $user = $this->login();

        $data = $this->getData();

        $response = $this->patchJson('/api/me/details', $data);

        $transformed = (new UserResource($user->fresh()))->resolve();

        $response->assertJsonFragment($transformed);

        $this->assertDatabaseHas('users', array_merge($data, ['id' => $user->id]));
    }

    
    public function test_partially_update_user_details()
    {
        $user = $this->login();

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
        ];

        $response = $this->patchJson('/api/me/details', $data);

        $transformed = (new UserResource($user->fresh()))->resolve();

        $response->assertJsonFragment($transformed);

        $this->assertDatabaseHas('users', array_merge($data, [
            'id' => $user->id,
            'birthday' => $user->birthday->format('Y-m-d'),
            'phone' => $user->phone,
            'address' => $user->address,
            'country_id' => $user->country_id,
        ]));
    }

    
    public function test_update_without_country_id()
    {
        $user = $this->login();

        $data = $this->getData();

        unset($data['country_id']);

        $response = $this->patchJson('/api/me/details', $data);

        $transformed = (new UserResource($user->fresh()))->resolve();

        $response->assertJsonFragment($transformed);

        $this->assertDatabaseHas('users', array_merge($data, ['id' => $user->id]));
    }

    
    public function test_update_with_invalid_date_format()
    {
        $this->login();

        $this->patchJson('/api/me/details', ['birthday' => 'foo'])
            ->assertStatus(422)
            ->assertJsonFragment([
                'birthday' => [
                    trans('validation.date', ['attribute' => 'birthday']),
                ],
            ]);
    }

    private function getData(array $attrs = []): array
    {
        return array_merge([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'birthday' => Carbon::now()->subYears(25)->format('Y-m-d'),
            'phone' => '(123) 456 789',
            'address' => 'some address 1',
            'country_id' => 688,
        ], $attrs);
    }
}
