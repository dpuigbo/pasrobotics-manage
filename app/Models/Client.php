<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'site',
        'travel_hours',
        'travel_days',
        'km',
        'tolls',
        'work_hour_rate',
        'travel_hour_rate',
        'diet_rate',
        'access_mgmt_fee',
    ];

    public function interventions()
    {
        return $this->hasMany(Intervention::class);
    }

    public function systems()
    {
        return $this->hasMany(System::class);
    }
}
