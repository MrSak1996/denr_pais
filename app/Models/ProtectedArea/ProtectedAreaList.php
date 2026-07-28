<?php

namespace App\Models\ProtectedArea;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;
use App\Models\HiredStaff\StaffInfo;
use App\Models\IECCEPA\IECCEPAReport;


class ProtectedAreaList extends Model
{

    protected $connection = 'mysql';

    protected $table = 'tbl_protected_area';


    protected $fillable = [
        'id',
        'pa_name',
        'pa_code',
        'created_at',
        'updated_at'

    ];

    //relationship for lawin patrolling
    public function patrolPlans()
    {
        return $this->hasMany(PatrolPlan::class, 'protected_area_id', 'id');
    }

    public function staff()
    {
        return $this->hasMany(StaffInfo::class, 'pa_id', 'id');
    }

    public function iecCepaReports()
    {
        return $this->hasMany(IECCEPAReport::class, 'pa_id', 'id');
    }
}
