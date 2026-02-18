<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Oil extends Model
{
    protected $fillable = [
        'name',
        'manufacturer',
        'cost_per_liter',
        'selling_price_per_liter',
        'specifications',
    ];

    protected $casts = [
        'cost_per_liter' => 'decimal:2',
        'selling_price_per_liter' => 'decimal:2',
    ];
}
