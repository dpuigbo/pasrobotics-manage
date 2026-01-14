<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComponentModel extends Model
{
    protected $table = 'component_models';

    protected $fillable = [
        'manufacturer_id',
        'type',
        'name',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'bool',
    ];

    public function templateVersions()
    {
        return $this->hasMany(\App\Models\ComponentModelTemplateVersion::class);
    }

    public function activeTemplateVersion()
    {
        return $this->templateVersions()
            ->where('status', 'active')
            ->orderByDesc('version')
            ->first();
    }
}
