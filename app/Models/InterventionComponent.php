<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterventionComponent extends Model
{
    protected $fillable = [
        'intervention_id','component_type','label','sort_order',
        'system_controller_unit_id','system_mechanical_unit_id','system_drive_unit_id',
        'template_version_id','schema_json','data_json'
    ];

    protected $casts = [
        'schema_json' => 'array',
        'data_json' => 'array',
    ];

    public function intervention(): BelongsTo { return $this->belongsTo(Intervention::class); }
    public function templateVersion(): BelongsTo { return $this->belongsTo(TemplateVersion::class); }

    public function systemControllerUnit(): BelongsTo { return $this->belongsTo(SystemControllerUnit::class); }
    public function systemMechanicalUnit(): BelongsTo { return $this->belongsTo(SystemMechanicalUnit::class); }
    public function systemDriveUnit(): BelongsTo { return $this->belongsTo(SystemDriveUnit::class); }
}
