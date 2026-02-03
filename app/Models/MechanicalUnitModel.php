<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MechanicalUnitModel extends ComponentModel
{
    protected $table = 'component_models';

    protected static function booted(): void
    {
        static::addGlobalScope('type', function (Builder $builder) {
            $builder->where('type', 'mechanical_unit');
        });

        static::creating(function (MechanicalUnitModel $model) {
            $model->type = 'mechanical_unit';
        });
    }

    // Override to ensure proper table reference in RelationManager
    public function templateVersions(): HasMany
    {
        return $this->hasMany(ComponentModelTemplateVersion::class, 'component_model_id', 'id');
    }
}
