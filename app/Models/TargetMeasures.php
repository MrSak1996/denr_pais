<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\TargetProgram;
use App\Models\TargetMeasureValue;

class TargetMeasures extends Model
{
    protected $table = 'target_measures';
    protected $fillable = ['target_program_id', 'description', 'order'];

    public function program(): BelongsTo
    {
        return $this->belongsTo(TargetProgram::class, 'target_program_id');
    }

    public function values()
    {
        return $this->hasMany(
            TargetMeasureValue::class,
            'target_measure_id'
        );
    }
}