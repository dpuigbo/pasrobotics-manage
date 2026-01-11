<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemDriveUnit extends Model
{
    protected $fillable = [
        'system_id','drive_unit_model_id','system_mechanical_unit_id','label','serial_number','manufactured_at','notes'
    ];

    protected $casts = ['manufactured_at' => 'date'];

    public function system(): BelongsTo { return $this->belongsTo(System::class); }
    public function driveUnitModel(): BelongsTo { return $this->belongsTo(DriveUnitModel::class); }
    public function mechanicalUnit(): BelongsTo { return $this->belongsTo(SystemMechanicalUnit::class, 'system_mechanical_unit_id'); }
}
