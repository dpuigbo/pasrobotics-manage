<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComponentModel extends Model
{
    protected $fillable = [
        'manufacturer_id',
        'type',   // controller | mechanical_unit | drive_unit
        'name',
        'notes',
        'axis_oils_config',
    ];

    protected $casts = [
        'axis_oils_config' => 'array',
    ];

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function templateVersions(): HasMany
    {
        return $this->hasMany(ComponentModelTemplateVersion::class);
    }

    public function getDisplayNameAttribute(): string
    {
        $m = $this->manufacturer?->name ?? 'N/A';
        return "{$m} · {$this->type} · {$this->name}";
    }
}
