<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    protected $fillable = ['name','component_type','manufacturer','notes'];

    public function versions(): HasMany
    {
        return $this->hasMany(TemplateVersion::class);
    }
}
