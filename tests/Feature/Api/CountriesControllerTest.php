<?php

namespace Tests\Feature\Api;

use Tests\Feature\ApiTestCase;
use App\Http\Resources\CountryResource;
use App\Models\Country;

class CountriesControllerTest extends ApiTestCase
{
    
    public function test_unauthenticated()
    {
        $this->getJson('/api/countries')->assertStatus(401);
    }

    
    public function test_get_all_countries()
    {
        $this->login();

        $this->getJson('/api/countries')
            ->assertOk()
            ->assertJson([
                'data' => CountryResource::collection(Country::all())->resolve(),
            ]);
    }
}
