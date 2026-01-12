<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plant extends Model
{
    protected $fillable = ['client_id', 'name', 'address', 'notes'];

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function machines(): HasMany { return $this->hasMany(Machine::class); }
    public function systems(): HasMany { return $this->hasMany(System::class); }
}
