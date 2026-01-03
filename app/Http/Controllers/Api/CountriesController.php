<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CountryResource;
use App\Repositories\Country\CountryRepository;

class CountriesController extends ApiController
{
    public function __construct(private readonly CountryRepository $countries)
    {
    }

    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return CountryResource::collection($this->countries->all());
    }
}
