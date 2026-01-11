<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visit extends Model
{
    protected $fillable = [
        'client_id','type','status','reference','title','performed_at','notes'
    ];

    protected $casts = [
        'performed_at' => 'datetime',
    ];

    // "Informes" por sistema (reusa tu tabla interventions)
    public function reports(): HasMany
    {
        return $this->hasMany(Intervention::class, 'visit_id');
    }
}
