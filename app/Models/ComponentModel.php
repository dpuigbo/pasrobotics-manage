<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComponentModel extends Model
{
    protected $fillable = [
        'manufacturer',
        'type',
        'model_name',
        'notes',
    ];
}
