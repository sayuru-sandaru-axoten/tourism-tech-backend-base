<?php

namespace App\Domains\Destinations\Http\Controllers;

use App\Domains\Destinations\Actions\CreateCountry;
use App\Domains\Destinations\Actions\DeleteCountry;
use App\Domains\Destinations\Actions\UpdateCountry;
use App\Domains\Destinations\Http\Requests\StoreCountryRequest;
use App\Domains\Destinations\Http\Requests\UpdateCountryRequest;
use App\Domains\Destinations\Http\Resources\CountryResource;
use App\Domains\Destinations\Models\Country;
use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class CountryController extends Controller
{
    public function index(): JsonResponse
    {
        $countries = Country::query()->orderBy('name')->get();

        return ApiResponse::success(CountryResource::collection($countries));
    }

    public function store(StoreCountryRequest $request, CreateCountry $action): JsonResponse
    {
        $country = $action->handle($request->validated());

        return ApiResponse::success(new CountryResource($country), 'Country created.', 201);
    }

    public function show(Country $country): JsonResponse
    {
        return ApiResponse::success(new CountryResource($country));
    }

    public function update(UpdateCountryRequest $request, Country $country, UpdateCountry $action): JsonResponse
    {
        $country = $action->handle($country, $request->validated());

        return ApiResponse::success(new CountryResource($country));
    }

    public function destroy(Country $country, DeleteCountry $action): JsonResponse
    {
        $action->handle($country);

        return ApiResponse::success(message: 'Country deleted.');
    }
}
