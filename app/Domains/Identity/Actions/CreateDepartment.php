<?php

namespace App\Domains\Identity\Actions;

use App\Domains\Identity\Models\Department;
use App\Support\Contracts\Action;
use Illuminate\Support\Str;

class CreateDepartment implements Action
{
    /**
     * @param  array{name: string, description?: string|null, is_active?: bool}  $data
     */
    public function handle(array $data): Department
    {
        return Department::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }
}
