<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportComponent extends Model
{
    protected $fillable = [
        'report_id','component_type','label','sort_order',
        'system_controller_unit_id','system_mechanical_unit_id','system_drive_unit_id',
        'template_version_id','schema_json','data_json'
    ];

    protected $casts = [
        'schema_json' => 'array',
        'data_json' => 'array',
    ];

    public function report(): BelongsTo { return $this->belongsTo(Report::class); }
    public function templateVersion(): BelongsTo { return $this->belongsTo(TemplateVersion::class); }
}
