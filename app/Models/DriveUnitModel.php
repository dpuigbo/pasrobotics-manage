<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class DriveUnitModel extends ComponentModel
{
    protected static function booted(): void
    {
        static::addGlobalScope('type', function (Builder $builder) {
            $builder->where('type', 'drive_unit');
        });

        static::creating(function (DriveUnitModel $model) {
            $model->type = 'drive_unit';
        });
    }
}
