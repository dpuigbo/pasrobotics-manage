<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class System extends Model
{
    protected $fillable = ['client_id', 'name', 'manufacturer', 'notes'];

    public function controllerUnit(): HasOne
    {
        return $this->hasOne(SystemControllerUnit::class);
    }

    public function mechanicalUnits(): HasMany
    {
        return $this->hasMany(SystemMechanicalUnit::class);
    }

    public function plant() { return $this->belongsTo(Plant::class); }
    
    public function machine() { return $this->belongsTo(Machine::class); }

    public function components()
    {
        return $this->hasMany(SystemComponent::class)->orderBy('position');
    }

    public function driveUnits(): HasMany
    {
        return $this->hasMany(SystemDriveUnit::class);
    }

    public function interventions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Intervention::class, 'intervention_systems')
            ->withTimestamps();
    }

    public function getDisplayNameAttribute(): string
    {
        $name = $this->name
            ?? $this->label
            ?? $this->code
            ?? 'Sistema';

        return "{$name} (#{$this->id})";
    }
}
