<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Models\TargetMeasureValue;

class TargetReport extends Model
{
    protected $table = 'target_reports';
    protected $fillable = ['protected_area_id', 'user_id', 'fiscal_year', 'status', 'submitted_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(TargetMeasureValue::class);
    }
    public function measureValues()
    {
        return $this->hasMany(
            TargetMeasureValue::class,
            'target_report_id'
        );
    }
}