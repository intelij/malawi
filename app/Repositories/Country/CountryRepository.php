<?php

namespace App\Repositories\Country;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use App\Models\Country;

interface CountryRepository
{
    /**
     * Create $key => $value array for all available countries.
     */
    public function lists(string $column = 'name', string $key = 'id'): Collection;

    /**
     * Get all available countries.
     *
     * @return EloquentCollection<Country>
     */
    public function all(): EloquentCollection;
}
