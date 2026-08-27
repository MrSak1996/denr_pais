<?php

namespace App\Models\Targets;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Target extends Model
{
    protected $table = 'targets';

    protected $fillable = [
        'user_id',
        'category',
        'program',
        'measure_text',
        'first_sem',
        'second_sem',
        'total',
        'remarks',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    // public function protectedArea(): BelongsTo
    // {
    //     return $this->belongsTo(ProtectedAreaList::class);
    // }
}