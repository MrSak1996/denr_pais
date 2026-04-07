<?php

namespace App\Models\ProtectedArea;
use Illuminate\Database\Eloquent\Model;

class PambResolution extends Model
{
    protected $fillable = [
        'resolution_no',
        'protected_area_id',
        'type_of_meeting',
        'focal_person',
        'resolution_title',
        'approved_pamb_no',
        'status',
        'date_of_meeting',
        'date_received_by_cdd',
        'date_received_by_focal',
        'date_submitted_released_by_focal',
        'date_received_by_oardts',
        'date_approved_by_pamb_chair',
        'date_emailed_bmb',
        'date_submitted_to_bmb_hardcopy'
    ];
}