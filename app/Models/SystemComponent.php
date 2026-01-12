<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemComponent extends Model
{
    protected $fillable = [
        'system_id',
        'component_model_id',
        'role',          // mechanical_unit | controller | drive_unit
        'label',         // editable (lo que pediste)
        'serial_number',
        'position',
        'notes',
    ];

    public function system(): BelongsTo
    {
        return $this->belongsTo(System::class);
    }

    public function componentModel(): BelongsTo
    {
        return $this->belongsTo(ComponentModel::class);
    }
}
