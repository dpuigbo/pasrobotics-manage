<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComponentModelTemplateVersion extends Model
{
    protected $table = 'component_model_template_versions';

    protected $fillable = ['component_model_id', 'version', 'is_active', 'name', 'schema'];

    protected $casts = [
        'is_active' => 'boolean',
        'schema' => 'array',
    ];

    public function componentModel(): BelongsTo
    {
        return $this->belongsTo(ComponentModel::class);
    }
}
