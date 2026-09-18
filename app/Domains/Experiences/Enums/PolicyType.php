<?php

namespace App\Domains\Experiences\Enums;

enum PolicyType: string
{
    case Cancellation = 'cancellation';
    case Payment = 'payment';
    case AgeRequirement = 'age_requirement';
    case HealthSafety = 'health_safety';
}
