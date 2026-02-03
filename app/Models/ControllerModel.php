<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    // Override to ensure proper table reference in RelationManager
    public function templateVersions(): HasMany
    {
        return $this->hasMany(ComponentModelTemplateVersion::class, 'component_model_id', 'id');
    }
}
