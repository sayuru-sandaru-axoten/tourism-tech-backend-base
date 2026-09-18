<?php

namespace App\Domains\Identity\Actions;

use App\Domains\Identity\Models\Department;
use App\Support\Contracts\Action;
use Illuminate\Support\Str;

class UpdateDepartment implements Action
{
    /**
     * @param  array{name?: string, description?: string|null, is_active?: bool}  $data
     */
    public function handle(Department $department, array $data): Department
    {
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $department->update($data);

        return $department;
    }
}
