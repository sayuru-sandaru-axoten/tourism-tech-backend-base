<?php

namespace App\Domains\Experiences\Actions;

use App\Domains\Experiences\Enums\ExperienceStatus;
use App\Domains\Experiences\Models\Experience;
use App\Support\Contracts\Action;
use Illuminate\Support\Str;

class CreateExperience implements Action
{
    /**
     * @param  array{destination_id: int, partner_id?: int|null, experience_type: string, title: string, slug?: string, short_description?: string|null, description?: string|null, duration_days?: int|null, duration_nights?: int|null, min_group_size?: int|null, max_group_size?: int|null, base_price?: float|null, currency?: string|null, status?: string, category_ids?: list<int>}  $data
     */
    public function handle(array $data): Experience
    {
        $status = $data['status'] ?? ExperienceStatus::Draft->value;

        $experience = Experience::create([
            'destination_id' => $data['destination_id'],
            'partner_id' => $data['partner_id'] ?? null,
            'experience_type' => $data['experience_type'],
            'title' => $data['title'],
            'slug' => $data['slug'] ?? Str::slug($data['title']),
            'short_description' => $data['short_description'] ?? null,
            'description' => $data['description'] ?? null,
            'duration_days' => $data['duration_days'] ?? null,
            'duration_nights' => $data['duration_nights'] ?? null,
            'min_group_size' => $data['min_group_size'] ?? null,
            'max_group_size' => $data['max_group_size'] ?? null,
            'base_price' => $data['base_price'] ?? null,
            'currency' => $data['currency'] ?? null,
            'status' => $status,
            'published_at' => $status === ExperienceStatus::Published->value ? now() : null,
        ]);

        if (isset($data['category_ids'])) {
            $experience->categories()->sync($data['category_ids']);
        }

        return $experience;
    }
}
