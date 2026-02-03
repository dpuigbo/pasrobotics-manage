<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class System extends Model
{
    protected $fillable = [
        'client_id',
        'plant_id',
        'machine_id',
        'manufacturer_id',
        'name',
        'notes',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function components(): HasMany
    {
        return $this->hasMany(SystemComponent::class);
    }

    public function controllerUnit(): HasOne
    {
        return $this->hasOne(SystemComponent::class)->where('type', 'controller');
    }

    public function mechanicalUnits(): HasMany
    {
        return $this->hasMany(SystemComponent::class)->where('type', 'mechanical_unit');
    }

    public function driveUnits(): HasMany
    {
        return $this->hasMany(SystemComponent::class)->where('type', 'drive_unit');
    }

    public function getDisplayNameAttribute(): string
    {
        $m = $this->manufacturer?->name ?? 'N/A';
        $base = $this->name ?: ('System #' . $this->id);
        return "{$m} - {$base} (#{$this->id})";
    }
}
