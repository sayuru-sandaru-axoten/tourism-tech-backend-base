<?php

namespace App\Domains\Identity\Http\Controllers;

use App\Domains\Identity\Actions\CreateDepartment;
use App\Domains\Identity\Actions\DeleteDepartment;
use App\Domains\Identity\Actions\UpdateDepartment;
use App\Domains\Identity\Http\Requests\StoreDepartmentRequest;
use App\Domains\Identity\Http\Requests\UpdateDepartmentRequest;
use App\Domains\Identity\Http\Resources\DepartmentResource;
use App\Domains\Identity\Models\Department;
use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller
{
    public function index(): JsonResponse
    {
        $departments = Department::query()->orderBy('name')->get();

        return ApiResponse::success(DepartmentResource::collection($departments));
    }

    public function store(StoreDepartmentRequest $request, CreateDepartment $action): JsonResponse
    {
        $department = $action->handle($request->validated());

        return ApiResponse::success(
            new DepartmentResource($department),
            'Department created.',
            201,
        );
    }

    public function show(Department $department): JsonResponse
    {
        return ApiResponse::success(new DepartmentResource($department));
    }

    public function update(UpdateDepartmentRequest $request, Department $department, UpdateDepartment $action): JsonResponse
    {
        $department = $action->handle($department, $request->validated());

        return ApiResponse::success(new DepartmentResource($department));
    }

    public function destroy(Department $department, DeleteDepartment $action): JsonResponse
    {
        $action->handle($department);

        return ApiResponse::success(message: 'Department deleted.');
    }
}
