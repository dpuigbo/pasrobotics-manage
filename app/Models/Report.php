<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Report extends Model
{
    protected $fillable = [
        'intervention_id','system_id','status','performed_start_at','performed_end_at','notes'
    ];

    protected $casts = [
        'performed_start_at' => 'datetime',
        'performed_end_at' => 'datetime',
    ];

    public function intervention(): BelongsTo { return $this->belongsTo(Intervention::class); }
    public function system(): BelongsTo { return $this->belongsTo(System::class); }

    public function components(): HasMany
    {
        return $this->hasMany(ReportComponent::class)->orderBy('sort_order');
    }
}
