<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\TargetReport;
use App\Models\TargetMeasures;

class TargetMeasureValue extends Model
{
    protected $table = 'target_measure_values';
    protected $fillable = [
        'target_report_id', 'target_measure_id',
        'first_sem', 'second_sem', 'total_target',
        'q1_rating', 'q2_rating', 'q3_rating', 'remarks',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(TargetReport::class, 'target_report_id');
    }

    public function measure(): BelongsTo
    {
        return $this->belongsTo(TargetMeasures::class, 'target_measure_id');
    }
}