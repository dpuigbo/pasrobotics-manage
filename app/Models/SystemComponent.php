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
}
