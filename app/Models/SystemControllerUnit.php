<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemControllerUnit extends Model
{
    protected $fillable = [
        'system_id','controller_model_id','serial_number','label','manufactured_at','notes'
    ];

    protected $casts = ['manufactured_at' => 'date'];

    public function system(): BelongsTo { return $this->belongsTo(System::class); }
    public function controllerModel(): BelongsTo { return $this->belongsTo(ControllerModel::class); }
}
