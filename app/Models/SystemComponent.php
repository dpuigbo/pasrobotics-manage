<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemComponent extends Model
{
    protected $fillable = [
        'system_id',
        'component_model_id',
        'type',
        'label',
        'serial_number',
        'axes_count',
        'notes',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function system(): BelongsTo
    {
        return $this->belongsTo(System::class);
    }

    public function componentModel(): BelongsTo
    {
        return $this->belongsTo(ComponentModel::class);
    }

    // Accessor attributes for type-specific model_id names (for backward compatibility)
    public function getControllerModelIdAttribute(): ?int
    {
        return $this->type === 'controller' ? $this->component_model_id : null;
    }

    public function getRobotModelIdAttribute(): ?int
    {
        return $this->type === 'mechanical_unit' ? $this->component_model_id : null;
    }

    public function getDriveUnitModelIdAttribute(): ?int
    {
        return $this->type === 'drive_unit' ? $this->component_model_id : null;
    }

    // Alias relationships pointing to the same componentModel (for backward compatibility with eager loading)
    public function controllerModel(): BelongsTo
    {
        return $this->componentModel();
    }

    public function robotModel(): BelongsTo
    {
        return $this->componentModel();
    }

    public function driveUnitModel(): BelongsTo
    {
        return $this->componentModel();
    }
}
