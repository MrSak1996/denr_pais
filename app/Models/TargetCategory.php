<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\TargetProgram;

class TargetCategory extends Model
{
    protected $table = 'target_categories';
    protected $fillable = ['name', 'order'];

    public function programs(): HasMany
    {
        return $this->hasMany(TargetProgram::class);
    }
}