<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComponentModel extends Model
{
    protected $fillable = [
        'manufacturer_id',
        'type',        // controller | drive_unit | mechanical_unit
        'model_name',
        'notes',
    ];

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function templateVersions(): HasMany
    {
        return $this->hasMany(ComponentModelTemplateVersion::class);
    }
}
