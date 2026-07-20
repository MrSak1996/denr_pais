<?php

namespace App\Models\IECCEPA;

use Illuminate\Database\Eloquent\Model;

class IECCEPAReport extends Model
{
    protected $table = 'tbl_iec_cepa';

    protected $fillable = [
        'id',
        'pa_id',
        'quarter',
        'activities',
        'details',
        'uploaded_by',
        'created_at',
        'updated_at',
    ];
}
