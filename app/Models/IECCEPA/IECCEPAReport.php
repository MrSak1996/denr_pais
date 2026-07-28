<?php

namespace App\Models\IECCEPA;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProtectedArea\ProtectedAreaList;

class IECCEPAReport extends Model
{
    protected $table = 'tbl_iec_cepa';

    protected $fillable = [
        'id',
        'pa_id',
        'quarter',
        'activities',
        'details',
        'strategy',
        'audience_type',
        'uploaded_by',
        'created_at',
        'updated_at',
    ];

     /**
     * Protected Area
     */
    public function protectedArea()
    {
        return $this->belongsTo(ProtectedAreaList::class, 'pa_id', 'id');
    }

    /**
     * Uploaded Files
     */
    public function uploadedFiles()
    {
        return $this->hasMany(IECCEPAModel::class, 'iec_cepa_id', 'id');
    }
}
