<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ComponentModel extends Model
{
    protected $fillable = ['manufacturer', 'type', 'model', 'variant', 'notes'];

    public function templateVersions(): HasMany
    {
        return $this->hasMany(ComponentModelTemplateVersion::class);
    }

    public function activeTemplate(): HasOne
    {
        return $this->hasOne(ComponentModelTemplateVersion::class)->where('is_active', true);
    }

    public function getDisplayNameAttribute(): string
    {
        return trim("{$this->manufacturer} {$this->model} " . ($this->variant ?? ''));
    }
}
