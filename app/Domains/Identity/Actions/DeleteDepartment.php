<?php

namespace App\Domains\Identity\Actions;

use App\Domains\Identity\Models\Department;
use App\Support\Contracts\Action;

class DeleteDepartment implements Action
{
    /**
     * Deletes the department. Staff members currently assigned to this department
     * will have their `department_id` set to null (nullOnDelete FK).
     */
    public function handle(Department $department): void
    {
        $department->delete();
    }
}
