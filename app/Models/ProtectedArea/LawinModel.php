<?php

namespace App\Models\ProtectedArea;

use Illuminate\Database\Eloquent\Model;

class LawinModel extends Model
{
    protected $connection = 'mysql';

    protected $table = 'tbl_patrol_plan';


    protected $fillable = [
        'id',
        'protected_area_id',
        'area',
        'target_patrol_sector',
        'conservation_targets',
        'target_patrol_route_distance',
        'target_patrol_sector_name',
        'target_patrol_sector_area',
        'target_patrol_sector_location',
        'target_patrol_period',
        'patrol_team_members',
        'data_manager',
        'number_of_patrollers',
        'created_at',
        'updated_at'

    ];
}
