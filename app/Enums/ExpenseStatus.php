<?php

namespace App\Enums;

enum ExpenseStatus: string
{
    case Planned = 'planned';
    case Contracted = 'contracted';
    case Paid = 'paid';
}
