<?php

namespace App\Enums;

enum VendorPaymentStatus: string
{
    case NoIniciado = 'no_iniciado';
    case PagadoParcialmente = 'pagado_parcialmente';
    case PagadoCompleto = 'pagado_completo';
}
