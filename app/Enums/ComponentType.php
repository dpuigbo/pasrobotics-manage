<?php

namespace App\Enums;

enum ComponentType: string
{
    case MechanicalUnit = 'mechanical_unit';
    case Controller     = 'controller';
    case DriveUnit      = 'drive_unit';
}
