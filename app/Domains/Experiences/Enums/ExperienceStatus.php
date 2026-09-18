<?php

namespace App\Domains\Experiences\Enums;

enum ExperienceStatus: string
{
    case Draft = 'draft';
    case Review = 'review';
    case Scheduled = 'scheduled';
    case Published = 'published';
    case Archived = 'archived';
}
