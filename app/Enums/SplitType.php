<?php

namespace App\Enums;

enum SplitType: string
{
    case FiftyFifty = '50_50';
    case Percent = 'percent';
    case Fixed = 'fixed';
}
