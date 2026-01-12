<?php

namespace App\Enums;

enum SystemComponentRole: string
{
    case MechanicalUnit = 'mechanical_unit';
    case Controller     = 'controller';
    case DriveUnit      = 'drive_unit';
}
