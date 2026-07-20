<?php

namespace App\Models\ProtectedArea;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProtectedArea\ProtectedAreaList;


class PatrolPlan extends Model
{
    protected $table = 'tbl_patrol_plan';

    protected $fillable = [
        'protected_area_id',
        'area',
        'target_patrol_sector',
        'conservation_targets',
        'target_patrol_sector_name',
        'target_patrol_sector_area',
        'target_patrol_sector_location',
        'target_patrol_route_distance',
        'target_patrol_period',
        'patrol_team_members',
        'data_manager',
        'number_of_patrollers',
    ];

    public function protectedArea()
    {
        return $this->belongsTo(ProtectedAreaList::class, 'protected_area_id', 'id');
    }

   
}
