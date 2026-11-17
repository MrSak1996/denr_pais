<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\TargetCategory;
use App\Models\TargetMeasures;

class TargetProgram extends Model
{
    protected $table = 'target_programs';
    protected $fillable = ['target_category_id', 'name', 'order'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(TargetCategory::class, 'target_category_id');
    }

    public function measures()
    {
        return $this->hasMany(TargetMeasures::class, 'target_program_id')
            ->orderBy('order');
    }
}
