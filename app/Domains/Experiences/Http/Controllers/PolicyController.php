<?php

namespace App\Domains\Experiences\Http\Controllers;

use App\Domains\Experiences\Actions\CreatePolicy;
use App\Domains\Experiences\Actions\DeletePolicy;
use App\Domains\Experiences\Actions\UpdatePolicy;
use App\Domains\Experiences\Http\Requests\StorePolicyRequest;
use App\Domains\Experiences\Http\Requests\UpdatePolicyRequest;
use App\Domains\Experiences\Http\Resources\PolicyResource;
use App\Domains\Experiences\Models\Experience;
use App\Domains\Experiences\Models\Policy;
use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class PolicyController extends Controller
{
    public function index(Experience $experience): JsonResponse
    {
        $policies = $experience->policies()->get();

        return ApiResponse::success(PolicyResource::collection($policies));
    }

    public function store(StorePolicyRequest $request, Experience $experience, CreatePolicy $action): JsonResponse
    {
        $policy = $action->handle($experience, $request->validated());

        return ApiResponse::success(new PolicyResource($policy), 'Policy created.', 201);
    }

    public function show(Policy $policy): JsonResponse
    {
        return ApiResponse::success(new PolicyResource($policy));
    }

    public function update(UpdatePolicyRequest $request, Policy $policy, UpdatePolicy $action): JsonResponse
    {
        $policy = $action->handle($policy, $request->validated());

        return ApiResponse::success(new PolicyResource($policy));
    }

    public function destroy(Policy $policy, DeletePolicy $action): JsonResponse
    {
        $action->handle($policy);

        return ApiResponse::success(message: 'Policy deleted.');
    }
}
