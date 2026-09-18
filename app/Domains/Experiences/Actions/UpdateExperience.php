<?php

namespace App\Domains\Experiences\Actions;

use App\Domains\Experiences\Enums\ExperienceStatus;
use App\Domains\Experiences\Models\Experience;
use App\Support\Contracts\Action;
use Illuminate\Support\Str;

class UpdateExperience implements Action
{
    /**
     * @param  array{destination_id?: int, partner_id?: int|null, experience_type?: string, title?: string, slug?: string, short_description?: string|null, description?: string|null, duration_days?: int|null, duration_nights?: int|null, min_group_size?: int|null, max_group_size?: int|null, base_price?: float|null, currency?: string|null, status?: string, category_ids?: list<int>}  $data
     */
    public function handle(Experience $experience, array $data): Experience
    {
        if (isset($data['title']) && ! isset($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        if (isset($data['status'])) {
            $newStatus = $data['status'];
            $wasPublished = $experience->status === ExperienceStatus::Published;
            $willBePublished = $newStatus === ExperienceStatus::Published->value;

            if ($willBePublished && ! $wasPublished) {
                $data['published_at'] = now();
            } elseif (! $willBePublished && $wasPublished) {
                $data['published_at'] = null;
            }
        }

        $categoryIds = $data['category_ids'] ?? null;
        unset($data['category_ids']);

        $experience->update($data);

        if ($categoryIds !== null) {
            $experience->categories()->sync($categoryIds);
        }

        return $experience;
    }
}
