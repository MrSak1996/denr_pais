<?php

namespace App\Models\HiredStaff;

use Illuminate\Database\Eloquent\Model;
use App\Models\HiredStaff\StaffInfo;

class AccomplishmentReport extends Model
{
    protected $table = 'tbl_accomplishment_reports';

    protected $fillable = [
        'accomplishments',
        'staff_id',
        'month',
        'submitted_at',
    ];

    protected $casts = [
        'accomplishments' => 'array',
    ];

    public function protectedAreaStaff()
    {
        return $this->belongsTo(
            StaffInfo::class,
            'staff_id',
            'id'
        );
    }
}