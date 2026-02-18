<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consumable extends Model
{
    protected $table = 'consumables';

    protected $fillable = [
        'name',
        'reference',
        'manufacturer',
        'cost',
        'selling_price',
        'unit_type',
        'notes',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'selling_price' => 'decimal:2',
    ];
}
