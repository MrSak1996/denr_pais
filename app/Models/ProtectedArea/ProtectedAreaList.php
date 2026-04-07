<?php

namespace App\Models\ProtectedArea;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

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
}
