<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateAssignment extends Model
{
    protected $fillable = [
        'component_type','template_version_id',
        'robot_model_id','controller_model_id','drive_unit_model_id',
        'priority'
    ];

    public function templateVersion(): BelongsTo { return $this->belongsTo(TemplateVersion::class); }
    public function robotModel(): BelongsTo { return $this->belongsTo(RobotModel::class); }
    public function controllerModel(): BelongsTo { return $this->belongsTo(ControllerModel::class); }
    public function driveUnitModel(): BelongsTo { return $this->belongsTo(DriveUnitModel::class); }
}
