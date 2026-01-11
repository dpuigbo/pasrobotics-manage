<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateVersion extends Model
{
    protected $fillable = ['template_id','version','is_published','schema_json'];

    protected $casts = [
        'is_published' => 'boolean',
        'schema_json' => 'array',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }
}
