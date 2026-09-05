<?php

namespace App\Enums;

enum IncidentStatus: string
{
    case Open = 'Open';
    case InProgress = 'In Progress';
    case Resolved = 'Resolved';
}
