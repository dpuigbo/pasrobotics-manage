<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class ControllerModel extends ComponentModel
{
    protected $table = 'component_models';

    protected static function booted(): void
    {
        static::addGlobalScope('type', function (Builder $builder) {
            $builder->where('type', 'controller');
        });

        static::creating(function (ControllerModel $model) {
            $model->type = 'controller';
        });
    }
}
