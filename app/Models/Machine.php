<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Machine extends Model
{
    protected $fillable = ['plant_id', 'name', 'code', 'notes'];

    public function plant(): BelongsTo { return $this->belongsTo(Plant::class); }
    public function systems(): HasMany { return $this->hasMany(System::class); }
}
