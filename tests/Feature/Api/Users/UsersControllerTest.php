<?php

namespace Tests\Feature\Api\Users;

use Event;
use Facades\Tests\Setup\UserFactory;
use Illuminate\Support\Arr;
use Tests\Feature\ApiTestCase;
use App\Events\User\Deleted;
use App\Events\User\UpdatedByAdmin;
use App\Http\Resources\UserResource;
use App\Models\Country;
use App\Models\Role;
use App\Models\User;
use App\Support\Enum\UserStatus;

class UsersControllerTest extends ApiTestCase
{
    
    public function test_test_only_authenticated_users_can_list_all_users()
    {
        $this->getJson('/api/users')->assertStatus(401);
    }

    
    public function test_test_get_users_without_permission()
    {
        $user = User::factory()->create();

        $this->actingAs($user, self::API_GUARD)
            ->getJson('/api/users')
            ->assertStatus(403);
    }

    
    public function test_test_paginate_all_users()
    {
        \DB::table('users')->delete();

        $user = $this->login();

        $users = User::factory()->times(20)->create();
        $users->push($user);

        $response = $this->getJson('/api/users');

        $transformed = UserResource::collection($users->sortBy('id')->take(20))->resolve();

        $this->assertEquals($response->json('data'), $transformed);
    }

    
    public function test_test_paginate_users_with_country_included()
    {
        $this->login();

        $country = Country::factory()->create();

        User::factory()->create(['country_id' => null]);
        User::factory()->create(['country_id' => $country->id]);

        $response = $this->getJson('/api/users?include=country')
            ->assertOk()
            ->json();

        $this->assertNull($response['data'][0]['country']);
        $this->assertNotNull($response['data'][1]['country_id']);
    }

    
    public function test_test_paginate_users_by_status()
    {
        $this->login();

        User::factory()->times(2)->create(['status' => UserStatus::ACTIVE]);
        User::factory()->times(5)->create(['status' => UserStatus::BANNED]);

        $response = $this->getJson('/api/users?filter[status]='.UserStatus::BANNED->value);

        $this->assertCount(5, $response->json('data'));
    }

    
    public function test_test_paginate_users_on_search()
    {
        $user = $this->login();

        $user1 = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@vanguardapp.io',
        ]);

        $user2 = User::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@vanguardapp.io',
        ]);

        $user3 = User::factory()->create([
            'first_name' => 'Brad',
            'last_name' => 'Pitt',
            'email' => 'b.pitt@vanguardapp.io',
        ]);

        $response = $this->getJson('/api/users?filter[search]=doe');

        $this->assertCount(2, $response->json('data'));
    }

    
    public function test_test_create_user()
    {
        $this->login();

        $newUser = User::factory()->make();

        $data = array_merge($newUser->toArray(), [
            'birthday' => $newUser->birthday->format('Y-m-d'),
            'role' => $newUser->role_id,
            'password' => '123123123',
            'password_confirmation' => '123123123',
        ]);

        $response = $this->postJson('api/users', $data);

        $expected = [
            'first_name' => $newUser->first_name,
            'last_name' => $newUser->last_name,
            'email' => $newUser->email,
            'username' => $newUser->username,
            'country_id' => $newUser->country_id,
            'birthday' => $newUser->birthday->format('Y-m-d'),
            'phone' => $newUser->phone,
            'address' => $newUser->address,
            'status' => UserStatus::ACTIVE,
            'role_id' => $newUser->role_id,
        ];

        $response->assertStatus(201)
            ->assertJsonFragment($expected);

        $this->assertDatabaseHas('users', $expected);
    }

    
    public function test_test_get_user()
    {
        $user = $this->login();

        $this->getJson("api/users/{$user->id}")
            ->assertOk()
            ->assertJson([
                'data' => (new UserResource($user->fresh()))->resolve(),
            ]);
    }

    
    public function test_test_get_user_which_does_not_exist()
    {
        $this->login();

        $this->getJson('api/users/222')->assertStatus(404);
    }

    
    public function test_test_update_user()
    {
        Event::fake([UpdatedByAdmin::class]);

        $user = $this->login();

        $data = [
            'email' => 'john.doe@test.com',
            'username' => 'john.doe',
            'password' => '123123123',
            'password_confirmation' => '123123123',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+38123456789',
            'address' => 'Some random address',
            'country_id' => Country::first()->id,
            'birthday' => '1990-10-18',
            'status' => UserStatus::BANNED,
            'role_id' => Role::whereName('User')->first()->id,
        ];

        $expected = Arr::except($data, ['password', 'password_confirmation']);
        $expected += ['id' => $user->id];

        $this->patchJson("api/users/{$user->id}", $data)
            ->assertOk()
            ->assertJsonFragment($expected);

        $this->assertDatabaseHas('users', $expected);

        Event::assertDispatched(UpdatedByAdmin::class);
    }

    
    public function test_test_update_only_specific_field()
    {
        Event::fake([UpdatedByAdmin::class]);

        $user = $this->login();

        $data = ['email' => 'john.doe@test.com'];

        $expected = array_merge(
            $user->toArray(),
            $data,
            ['birthday' => $user->birthday->format('Y-m-d')]
        );

        $expected = Arr::except($expected, ['created_at', 'updated_at', 'avatar', 'role']);

        $this->patchJson("api/users/{$user->id}", $data)
            ->assertOk()
            ->assertJsonFragment($expected);

        $this->assertDatabaseHas('users', $expected);

        Event::assertDispatched(UpdatedByAdmin::class);
    }

    
    public function test_test_delete_user()
    {
        Event::fake([Deleted::class]);

        $user = $this->login();

        $user2 = User::factory()->create();

        $this->deleteJson("api/users/{$user2->id}")
            ->assertOk()
            ->assertJson(['success' => true]);

        Event::assertDispatched(Deleted::class);
    }

    
    public function test_test_delete_yourself()
    {
        $user = $this->login();

        $this->deleteJson("api/users/{$user->id}")
            ->assertStatus(403)
            ->assertJson(['message' => 'You cannot delete yourself.']);
    }

    protected function login()
    {
        $user = UserFactory::withPermissions('users.manage')->create();

        $this->be($user, self::API_GUARD);

        return $user;
    }
}
