<?php
namespace App\Enum;

enum WatchingStatus: string
{
    case PLANNED   = 'PLANNED';   // à voir
    case WATCHING  = 'WATCHING';  // en cours
    case COMPLETED = 'COMPLETED'; // vu
    case ON_HOLD   = 'ON_HOLD';   // en pause
    case DROPPED   = 'DROPPED';   // abandonné
}
