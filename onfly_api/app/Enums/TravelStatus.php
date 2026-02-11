<?php

namespace App\Enums;

enum TravelStatus: string
{
    case Requested = 'requested';
    case Approved = 'approved';
    case Canceled = 'canceled';
}
