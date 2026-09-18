<?php

namespace App\Domains\Experiences\Enums;

enum ExperienceType: string
{
    case Package = 'package';
    case Activity = 'activity';
    case HotelStay = 'hotel_stay';
    case Transfer = 'transfer';
    case GuideService = 'guide_service';
}
