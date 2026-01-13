<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Manufacturer extends Model
{
    protected $fillable = ['name', 'is_active', 'sort'];

    public function systems(): HasMany
    {
        return $this->hasMany(System::class);
    }

    public function componentModels(): HasMany
    {
        return $this->hasMany(ComponentModel::class);
    }
}
