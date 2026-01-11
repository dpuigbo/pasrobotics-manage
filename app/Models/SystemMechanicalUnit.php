<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SystemMechanicalUnit extends Model
{
    protected $fillable = [
        'system_id','robot_model_id','label','serial_number','manufactured_at','axes_count','notes'
    ];

    protected $casts = ['manufactured_at' => 'date'];

    public function system(): BelongsTo { return $this->belongsTo(System::class); }
    public function robotModel(): BelongsTo { return $this->belongsTo(RobotModel::class); }

    public function driveUnits(): HasMany
    {
        return $this->hasMany(SystemDriveUnit::class);
    }
}
