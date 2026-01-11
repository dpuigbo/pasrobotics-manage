<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Visit;

class Intervention extends Model
{
    protected $fillable = [
        'system_id','type','status','reference','title','performed_at','notes'
    ];

    protected $casts = [
        'performed_at' => 'datetime',
    ];

    public function system(): BelongsTo
    {
        return $this->belongsTo(System::class);
    }

    public function components(): HasMany
    {
        return $this->hasMany(InterventionComponent::class)->orderBy('sort_order');
    }

    public function visit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Visit::class, 'visit_id');
    }
}
