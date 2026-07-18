<?php

namespace App\Enums;

enum RsvpStatus: string
{
    case Pendiente = 'pendiente';
    case Confirmado = 'confirmado';
    case NoAsiste = 'no_asiste';
}
