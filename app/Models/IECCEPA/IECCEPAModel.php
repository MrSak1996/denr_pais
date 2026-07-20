<?php

namespace App\Models\IECCEPA;

use Illuminate\Database\Eloquent\Model;

class IECCEPAModel extends Model
{
    protected $table = 'tbl_uploaded_files';

    protected $fillable = [
        'id',
        'iec_cepa_id',
        'report_type',
        'file_id',
        'file_name',
        'file_url',
        'created_at',
        'updated_at',
    ];
}
