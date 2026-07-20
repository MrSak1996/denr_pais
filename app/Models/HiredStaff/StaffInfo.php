<?php

namespace App\Models\HiredStaff;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProtectedArea\ProtectedAreaList;

class StaffInfo extends Model
{
    protected $table = 'tbl_pa_staff'; // adjust if needed


    protected $fillable = [
        'id',
        'name',
        'pa_id',
        'position',
    ];
    public function protectedArea()
    {
        return $this->belongsTo(
            ProtectedAreaList::class,
            'pa_id',
            'id'
        );
    }
     
}
