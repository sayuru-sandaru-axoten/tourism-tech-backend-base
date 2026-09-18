<?php

namespace App\Domains\Partners\Enums;

enum PartnerType: string
{
    case Hotel = 'hotel';
    case TransportProvider = 'transport_provider';
    case TourOperator = 'tour_operator';
    case Guide = 'guide';
    case ActivityProvider = 'activity_provider';
}
