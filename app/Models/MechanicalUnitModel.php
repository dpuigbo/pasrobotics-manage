<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class MechanicalUnitModel extends ComponentModel
{
    protected static function booted(): void
    {
        static::addGlobalScope('type', function (Builder $builder) {
            $builder->where('type', 'mechanical_unit');
        });

        static::creating(function (MechanicalUnitModel $model) {
            $model->type = 'mechanical_unit';
        });
    }
}
