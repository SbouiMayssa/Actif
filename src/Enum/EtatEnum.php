<?php
namespace App\Enum;

enum EtatEnum: string
{
    case EN_SERVICE = 'en_service';
    case EN_PANNE = 'en_panne';
    case HORS_SERVICE = 'hors_service';
}